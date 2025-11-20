// test-processor.js - Custom functions untuk Artillery Pusher testing
'use strict';

const Pusher = require('pusher-js');

module.exports = {
  setupMonitoring,
  connectPusher,
  subscribeToStream,
  subscribeToMirrorState,
  disconnectPusher,
  captureLoadTime,
  recordPageLoad,
  recordChunkLatency,
  recordChatLatency,
  recordChatHistoryLoad,
  collectMetrics,
  generateReport
};

// Tracking objects
const connections = new Map();
const metrics = {
  connectTimes: [],
  subscribeTimes: [],
  chunkLatencies: [],
  chatLatencies: [],
  chatHistoryLatencies: [],
  mirrorStateLatencies: [],
  errors: []
};

function setupMonitoring(context, events, done) {
  context.vars.startTime = Date.now();
  context.vars.metrics = metrics;

  // Disable Pusher logging in production tests
  Pusher.logToConsole = false;

  return done();
}

function connectPusher(context, events, done) {
  const startTime = Date.now();
  const pusherKey = context.vars.pusherKey || '81f8af2b6681fa8ada90';
  const cluster = context.vars.pusherCluster || 'ap1';

  const pusher = new Pusher(pusherKey, {
    cluster: cluster,
    forceTLS: true,
    enabledTransports: ['ws', 'wss']
  });

  pusher.connection.bind('connected', () => {
    const latency = Date.now() - startTime;
    metrics.connectTimes.push(latency);
    events.emit('counter', 'pusher.connect.success', 1);
    events.emit('histogram', 'pusher.connect.latency', latency);
  });

  pusher.connection.bind('error', (error) => {
    metrics.errors.push({ type: 'connection', error: error.message });
    events.emit('counter', 'pusher.connect.error', 1);
  });

  pusher.connection.bind('disconnected', () => {
    events.emit('counter', 'pusher.disconnected', 1);
  });

  context.vars.pusher = pusher;
  context.vars.channels = {};
  connections.set(context.vars.$uuid, pusher);

  // Wait for connection
  setTimeout(done, 500);
}

function subscribeToStream(context, events, done) {
  const pusher = context.vars.pusher;
  const streamSlug = context.vars.streamSlug;
  const startTime = Date.now();

  if (!pusher) {
    events.emit('counter', 'pusher.subscribe.error', 1);
    return done();
  }

  // Subscribe ke channel berdasarkan slug (sesuai implementasi)
  const channelName = `live-stream.${streamSlug}`;
  const channel = pusher.subscribe(channelName);

  channel.bind('pusher:subscription_succeeded', () => {
    const latency = Date.now() - startTime;
    metrics.subscribeTimes.push(latency);
    events.emit('counter', 'pusher.subscribe.success', 1);
    events.emit('histogram', 'pusher.subscribe.latency', latency);
  });

  channel.bind('pusher:subscription_error', (error) => {
    metrics.errors.push({ type: 'subscription', channel: channelName, error });
    events.emit('counter', 'pusher.subscribe.error', 1);
  });

  // Bind to events
  channel.bind('App\\Events\\NewChunk', (data) => {
    events.emit('counter', 'chunk.received', 1);
  });

  channel.bind('App\\Events\\ViewerJoined', (data) => {
    context.vars.viewerCount = data.viewerCount;
    events.emit('counter', 'viewer.joined', 1);
  });

  channel.bind('App\\Events\\ViewerLeft', (data) => {
    context.vars.viewerCount = data.viewerCount;
    events.emit('counter', 'viewer.left', 1);
  });

  channel.bind('App\\Events\\StreamEnded', () => {
    events.emit('counter', 'stream.ended', 1);
  });

  channel.bind('App\\Events\\ChatMessageSent', (data) => {
    events.emit('counter', 'chat.message.received', 1);
  });

  channel.bind('App\\Events\\ClassificationReady', (data) => {
    events.emit('counter', 'classification.received', 1);
  });

  context.vars.channels.stream = channel;

  return done();
}

function subscribeToMirrorState(context, events, done) {
  const channel = context.vars.channels.stream;
  const startTime = Date.now();

  if (!channel) {
    return done();
  }

  channel.bind('App\\Events\\MirrorStateChanged', (data) => {
    const latency = Date.now() - startTime;
    metrics.mirrorStateLatencies.push(latency);
    events.emit('histogram', 'mirror.state.latency', latency);
    events.emit('counter', 'mirror.state.changed', 1);

    // Reset startTime for next event
    startTime = Date.now();
  });

  return done();
}

function disconnectPusher(context, events, done) {
  const pusher = context.vars.pusher;

  if (pusher) {
    // Unsubscribe from all channels
    Object.values(context.vars.channels || {}).forEach(channel => {
      if (channel) {
        pusher.unsubscribe(channel.name);
      }
    });

    pusher.disconnect();
    connections.delete(context.vars.$uuid);
    events.emit('counter', 'pusher.disconnect.success', 1);
  }

  return done();
}

function captureLoadTime(req, res, context, events, done) {
  const loadTime = res.timings.phases.total;
  events.emit('histogram', 'page.index.load.time', loadTime);
  return done();
}

function recordPageLoad(req, res, context, events, done) {
  const loadTime = res.timings.phases.total;
  context.vars.pageLoadTime = loadTime;
  events.emit('histogram', 'page.stream.load.time', loadTime);
  return done();
}

function recordChunkLatency(req, res, context, events, done) {
  if (res.statusCode === 200) {
    const latency = res.timings.phases.total;
    metrics.chunkLatencies.push(latency);
    events.emit('histogram', 'chunk.load.latency', latency);
    events.emit('counter', 'chunk.load.success', 1);
  } else {
    events.emit('counter', 'chunk.load.notfound', 1);
  }
  return done();
}

function recordChatLatency(req, res, context, events, done) {
  if (res.statusCode === 200) {
    const latency = res.timings.phases.total;
    metrics.chatLatencies.push(latency);
    events.emit('histogram', 'chat.send.latency', latency);
    events.emit('counter', 'chat.send.success', 1);
  } else if (res.statusCode === 429) {
    events.emit('counter', 'chat.throttled', 1);
  } else {
    events.emit('counter', 'chat.send.error', 1);
  }
  return done();
}

function recordChatHistoryLoad(req, res, context, events, done) {
  if (res.statusCode === 200) {
    const latency = res.timings.phases.total;
    metrics.chatHistoryLatencies.push(latency);
    events.emit('histogram', 'chat.history.latency', latency);
    events.emit('counter', 'chat.history.success', 1);
  } else {
    events.emit('counter', 'chat.history.error', 1);
  }
  return done();
}

function collectMetrics(context, events, done) {
  const stats = {
    connect: calculateStats(metrics.connectTimes),
    subscribe: calculateStats(metrics.subscribeTimes),
    chunk: calculateStats(metrics.chunkLatencies),
    chat: calculateStats(metrics.chatLatencies),
    chatHistory: calculateStats(metrics.chatHistoryLatencies),
    mirrorState: calculateStats(metrics.mirrorStateLatencies)
  };

  console.log('\n========================================');
  console.log('    PUSHER PERFORMANCE METRICS');
  console.log('========================================\n');

  console.log('📡 Connection Latency:');
  console.log(`   Min: ${stats.connect.min}ms | Max: ${stats.connect.max}ms`);
  console.log(`   Avg: ${stats.connect.avg.toFixed(2)}ms | P95: ${stats.connect.p95}ms\n`);

  console.log('📺 Subscribe Latency:');
  console.log(`   Min: ${stats.subscribe.min}ms | Max: ${stats.subscribe.max}ms`);
  console.log(`   Avg: ${stats.subscribe.avg.toFixed(2)}ms | P95: ${stats.subscribe.p95}ms\n`);

  console.log('🎬 Chunk Load Latency:');
  console.log(`   Min: ${stats.chunk.min}ms | Max: ${stats.chunk.max}ms`);
  console.log(`   Avg: ${stats.chunk.avg.toFixed(2)}ms | P95: ${stats.chunk.p95}ms\n`);

  console.log('💬 Chat Send Latency:');
  console.log(`   Min: ${stats.chat.min}ms | Max: ${stats.chat.max}ms`);
  console.log(`   Avg: ${stats.chat.avg.toFixed(2)}ms | P95: ${stats.chat.p95}ms\n`);

  console.log('📜 Chat History Load:');
  console.log(`   Min: ${stats.chatHistory.min}ms | Max: ${stats.chatHistory.max}ms`);
  console.log(`   Avg: ${stats.chatHistory.avg.toFixed(2)}ms | P95: ${stats.chatHistory.p95}ms\n`);

  console.log(`❌ Total Errors: ${metrics.errors.length}`);
  console.log(`🔗 Peak Connections: ${connections.size}`);
  console.log('\n========================================\n');

  return done();
}

function generateReport(context, events, done) {
  const fs = require('fs');
  const path = require('path');

  const report = {
    implementation: 'pusher',
    timestamp: new Date().toISOString(),
    duration: Date.now() - context.vars.startTime,
    metrics: {
      connection: calculateStats(metrics.connectTimes),
      subscribe: calculateStats(metrics.subscribeTimes),
      chunkLoad: calculateStats(metrics.chunkLatencies),
      chatSend: calculateStats(metrics.chatLatencies),
      chatHistory: calculateStats(metrics.chatHistoryLatencies),
      mirrorState: calculateStats(metrics.mirrorStateLatencies)
    },
    errors: metrics.errors,
    peakConnections: connections.size
  };

  // Create reports directory if not exists
  const reportsDir = path.join(process.cwd(), 'reports');
  if (!fs.existsSync(reportsDir)) {
    fs.mkdirSync(reportsDir, { recursive: true });
  }

  // Save report
  const filename = `pusher-${Date.now()}.json`;
  fs.writeFileSync(
    path.join(reportsDir, filename),
    JSON.stringify(report, null, 2)
  );

  console.log(`📊 Report saved to: reports/${filename}`);

  return done();
}

// Helper functions
function average(arr) {
  return arr.length > 0 ? arr.reduce((a, b) => a + b, 0) / arr.length : 0;
}

function calculateStats(arr) {
  if (arr.length === 0) {
    return { min: 0, max: 0, avg: 0, median: 0, p95: 0, p99: 0 };
  }

  const sorted = [...arr].sort((a, b) => a - b);
  return {
    min: sorted[0],
    max: sorted[sorted.length - 1],
    avg: average(arr),
    median: sorted[Math.floor(sorted.length / 2)],
    p95: sorted[Math.floor(sorted.length * 0.95)] || sorted[sorted.length - 1],
    p99: sorted[Math.floor(sorted.length * 0.99)] || sorted[sorted.length - 1],
    count: arr.length
  };
}
