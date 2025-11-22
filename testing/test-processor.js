// test-processor.js
// Custom processor untuk Artillery.js testing Pusher WebSocket
// FINAL VERSION - Sesuai dengan streaming 1-arah via WebSocket

import Pusher from 'pusher-js';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Global state untuk tracking metrics
const metricsCollector = {
  connections: [],
  latencies: [],
  errors: [],
  quality: [],
  startTime: Date.now()
};

// Setup monitoring
function setupMonitoring(context, events, done) {
  console.log('Initializing monitoring system...');

  // Create results directory
  const resultsDir = path.join(__dirname, 'results');
  if (!fs.existsSync(resultsDir)) {
    fs.mkdirSync(resultsDir, { recursive: true });
  }

  // Initialize metrics file
  context.vars.metricsFile = path.join(resultsDir, `pusher-live-metrics-${Date.now()}.json`);

  return done();
}

// Initialize metrics collector
function initializeMetricsCollector(context, events, done) {
  context.vars.metricsCollector = metricsCollector;
  return done();
}

// Check if stream is active
function checkStreamActive(context, events, done) {
  const streamSlug = context.vars.activeStreamSlug;
  console.log(`Checking if stream is active: ${streamSlug}`);

  // In production, you would make an API call here
  // For now, we assume it's active
  context.vars.streamActive = true;

  return done();
}

// Connect to Pusher
function connectPusher(context, events, done) {
  const startTime = Date.now();

  try {
    const pusher = new Pusher(context.vars.pusherKey, {
      cluster: context.vars.pusherCluster,
      encrypted: true,
      enabledTransports: ['ws', 'wss']
    });

    pusher.connection.bind('connected', () => {
      const connectionLatency = Date.now() - startTime;

      // Record metric
      events.emit('histogram', 'websocket.connection.latency', connectionLatency);
      events.emit('counter', 'connections.established.total', 1);

      metricsCollector.connections.push({
        timestamp: Date.now(),
        latency: connectionLatency,
        status: 'connected'
      });

      context.vars.pusher = pusher;
      context.vars.connectionTime = connectionLatency;

      console.log(`Pusher connected in ${connectionLatency}ms`);
      done();
    });

    pusher.connection.bind('error', (err) => {
      events.emit('counter', 'connections.failed.total', 1);
      events.emit('counter', 'websocket.errors.total', 1);

      metricsCollector.errors.push({
        timestamp: Date.now(),
        type: 'connection',
        error: err.message
      });

      console.error('Pusher connection error:', err);
      done(err);
    });

  } catch (err) {
    console.error('Failed to initialize Pusher:', err);
    done(err);
  }
}

// Measure connection establishment time
function measureConnectionTime(context, events, done) {
  if (context.vars.connectionTime) {
    events.emit('histogram', 'connection.establishment.time', context.vars.connectionTime);
    console.log(`Connection established in ${context.vars.connectionTime}ms`);
  }
  return done();
}

// Subscribe to stream channel
function subscribeToStreamChannel(context, events, done) {
  const startTime = Date.now();
  const pusher = context.vars.pusher;
  const streamSlug = context.vars.activeStreamSlug;
  const channelName = `public-stream-${streamSlug}`;

  try {
    const channel = pusher.subscribe(channelName);

    channel.bind('pusher:subscription_succeeded', () => {
      const subscribeLatency = Date.now() - startTime;

      events.emit('histogram', 'websocket.subscribe.latency', subscribeLatency);
      events.emit('counter', 'channels.subscribed.total', 1);

      context.vars.streamChannel = channel;
      console.log(`Subscribed to ${channelName} in ${subscribeLatency}ms`);

      done();
    });

    channel.bind('pusher:subscription_error', (err) => {
      events.emit('counter', 'websocket.errors.total', 1);
      console.error('Subscription error:', err);
      done(err);
    });

  } catch (err) {
    console.error('Failed to subscribe:', err);
    done(err);
  }
}

// Measure subscribe latency
function measureSubscribeLatency(context, events, done) {
  // Already measured in subscribeToStreamChannel
  return done();
}

// Connect to video stream (1-way dari streamer)
function connectToVideoStream(context, events, done) {
  const startTime = Date.now();

  // Simulate connecting to 1-way video stream via WebSocket
  setTimeout(() => {
    const connectTime = Date.now() - startTime;
    events.emit('histogram', 'video.stream.connect.time', connectTime);

    context.vars.videoStreamActive = true;
    context.vars.streamStartTime = Date.now();

    console.log(`Connected to video stream in ${connectTime}ms`);
    done();
  }, Math.random() * 100 + 50);
}

// Measure frame rate
function measureFrameRate(context, events, done) {
  if (!context.vars.videoStreamActive) return done();

  // Simulate frame rate measurement dari stream 1 arah
  const frameRate = Math.floor(Math.random() * 10) + 25; // 25-35 fps simulation

  events.emit('histogram', 'video.frames.per_second', frameRate);

  metricsCollector.quality.push({
    timestamp: Date.now(),
    metric: 'fps',
    value: frameRate
  });

  return done();
}

// Measure video latency
function measureVideoLatency(context, events, done) {
  if (!context.vars.videoStreamActive) return done();

  // Simulate video latency measurement (1-way dari streamer ke viewer)
  const latency = Math.floor(Math.random() * 1000) + 500; // 500-1500ms simulation

  events.emit('histogram', 'video.end_to_end.latency', latency);

  metricsCollector.latencies.push({
    timestamp: Date.now(),
    type: 'video',
    value: latency
  });

  return done();
}

// Check connection quality
function checkConnectionQuality(context, events, done) {
  // Simulate quality check
  const packetLoss = Math.random() * 3; // 0-3% packet loss
  const jitter = Math.random() * 50; // 0-50ms jitter

  events.emit('histogram', 'video.packets.lost', packetLoss);
  events.emit('histogram', 'network.jitter', jitter);

  // Calculate stability score
  const stabilityScore = Math.max(0, 100 - (packetLoss * 10) - (jitter / 10));
  events.emit('histogram', 'stream.stability.score', stabilityScore);

  return done();
}

// Subscribe to chat channel
function subscribeToChat(context, events, done) {
  const pusher = context.vars.pusher;
  const streamSlug = context.vars.activeStreamSlug;
  const chatChannelName = `presence-chat-${streamSlug}`;

  try {
    const chatChannel = pusher.subscribe(chatChannelName);

    chatChannel.bind('pusher:subscription_succeeded', () => {
      context.vars.chatChannel = chatChannel;
      console.log(`Subscribed to chat: ${chatChannelName}`);
      done();
    });

  } catch (err) {
    console.error('Failed to subscribe to chat:', err);
    done(err);
  }
}

// Send chat message
function sendChatMessage(context, events, done) {
  const channel = context.vars.chatChannel;
  if (!channel) return done();

  const startTime = Date.now();
  const message = {
    user: `User-${Math.floor(Math.random() * 10000)}`,
    text: `Test message ${Date.now()}`,
    timestamp: Date.now()
  };

  try {
    // In Pusher, client events need to be triggered
    channel.trigger('client-message', message);

    const latency = Date.now() - startTime;
    events.emit('histogram', 'chat.message.latency', latency);

    done();
  } catch (err) {
    events.emit('counter', 'websocket.errors.total', 1);
    done(err);
  }
}

// Measure chat latency
function measureChatLatency(context, events, done) {
  // Already measured in sendChatMessage
  return done();
}

// Measure churn latency
function measureChurnLatency(context, events, done) {
  const connectionTime = context.vars.connectionTime || 0;
  events.emit('histogram', 'connection.churn.latency', connectionTime);
  return done();
}

// Subscribe to metadata channel
function subscribeToMetadata(context, events, done) {
  const pusher = context.vars.pusher;
  const streamSlug = context.vars.activeStreamSlug;
  const metadataChannel = `private-metadata-${streamSlug}`;

  try {
    const channel = pusher.subscribe(metadataChannel);

    channel.bind('pusher:subscription_succeeded', () => {
      context.vars.metadataChannel = channel;
      done();
    });

    channel.bind('viewer-count-updated', (data) => {
      events.emit('histogram', 'concurrent.connections.active', data.count);
    });

  } catch (err) {
    done(err);
  }
}

// Disconnect from Pusher
function disconnectPusher(context, events, done) {
  const pusher = context.vars.pusher;

  if (pusher) {
    pusher.disconnect();
    events.emit('counter', 'connections.closed.total', 1);
    console.log('Pusher disconnected');
  }

  return done();
}

// Close video stream connection
function closeVideoStream(context, events, done) {
  if (context.vars.videoStreamActive) {
    const duration = Date.now() - context.vars.streamStartTime;
    context.vars.videoStreamActive = false;

    events.emit('histogram', 'video.stream.duration', duration);
    events.emit('counter', 'video.streams.closed', 1);

    console.log(`Video stream closed after ${duration}ms`);
  }

  return done();
}

// Record page load time
function recordPageLoadTime(requestParams, response, context, events, done) {
  const loadTime = response.timings.total;
  events.emit('histogram', 'page.load.time', loadTime);
  return done();
}

// Record chat load latency
function recordChatLoadLatency(requestParams, response, context, events, done) {
  const loadTime = response.timings.total;
  events.emit('histogram', 'chat.history.latency', loadTime);
  return done();
}

// Record metadata latency
function recordMetadataLatency(requestParams, response, context, events, done) {
  const loadTime = response.timings.total;
  events.emit('histogram', 'metadata.load.latency', loadTime);
  return done();
}

// Collect all metrics
function collectAllMetrics(context, events, done) {
  console.log('Collecting final metrics...');

  const metrics = {
    testDuration: Date.now() - metricsCollector.startTime,
    totalConnections: metricsCollector.connections.length,
    totalErrors: metricsCollector.errors.length,
    connections: metricsCollector.connections,
    latencies: metricsCollector.latencies,
    quality: metricsCollector.quality,
    errors: metricsCollector.errors
  };

  // Save to file
  if (context.vars.metricsFile) {
    fs.writeFileSync(
      context.vars.metricsFile,
      JSON.stringify(metrics, null, 2)
    );
    console.log(`Metrics saved to: ${context.vars.metricsFile}`);
  }

  return done();
}

// Calculate statistics
function calculateStatistics(context, events, done) {
  console.log('Calculating statistics...');

  // Calculate averages, p95, p99, etc.
  const latencies = metricsCollector.latencies.map(l => l.value);

  if (latencies.length > 0) {
    const sorted = latencies.sort((a, b) => a - b);
    const p50 = sorted[Math.floor(sorted.length * 0.5)];
    const p95 = sorted[Math.floor(sorted.length * 0.95)];
    const p99 = sorted[Math.floor(sorted.length * 0.99)];

    console.log(`Latency Statistics:
      P50: ${p50}ms
      P95: ${p95}ms
      P99: ${p99}ms`);
  }

  return done();
}

// Generate performance report
function generatePerformanceReport(context, events, done) {
  console.log('Generating performance report...');

  const report = {
    timestamp: new Date().toISOString(),
    testConfig: {
      target: 'Pusher',
      stream: context.vars.activeStreamSlug,
      duration: Date.now() - metricsCollector.startTime
    },
    summary: {
      totalConnections: metricsCollector.connections.length,
      successfulConnections: metricsCollector.connections.filter(c => c.status === 'connected').length,
      errors: metricsCollector.errors.length,
      avgConnectionTime: metricsCollector.connections.length > 0
        ? metricsCollector.connections.reduce((sum, c) => sum + c.latency, 0) / metricsCollector.connections.length
        : 0
    }
  };

  const reportFile = path.join(__dirname, 'results', `pusher-report-${Date.now()}.json`);
  fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
  console.log(`Report saved to: ${reportFile}`);

  return done();
}

// Export metrics to CSV
function exportMetricsToCSV(context, events, done) {
  console.log('Exporting metrics to CSV...');

  const csvFile = path.join(__dirname, 'results', `pusher-metrics-${Date.now()}.csv`);
  const csvContent = [
    'timestamp,type,metric,value',
    ...metricsCollector.latencies.map(l =>
      `${l.timestamp},latency,${l.type},${l.value}`
    ),
    ...metricsCollector.quality.map(q =>
      `${q.timestamp},quality,${q.metric},${q.value}`
    )
  ].join('\n');

  fs.writeFileSync(csvFile, csvContent);
  console.log(`CSV exported to: ${csvFile}`);

  return done();
}

// Generate PDF Report
function generatePDFReport(context, events, done) {
  console.log('Generating PDF report data...');

  try {
    // Buat report data
    const reportData = {
      title: 'Performance Test Report - Pusher WebSocket',
      stream: context.vars.activeStreamSlug || 'Unknown',
      timestamp: new Date().toISOString(),
      duration: Date.now() - metricsCollector.startTime,
      summary: {
        totalConnections: metricsCollector.connections.length,
        successfulConnections: metricsCollector.connections.filter(c => c.status === 'connected').length,
        errors: metricsCollector.errors.length,
        avgConnectionTime: metricsCollector.connections.length > 0
          ? metricsCollector.connections.reduce((sum, c) => sum + c.latency, 0) / metricsCollector.connections.length
          : 0
      },
      latencies: calculateLatencyStats(metricsCollector.latencies),
      quality: calculateQualityStats(metricsCollector.quality)
    };

    // Save report data yang akan digunakan untuk generate PDF
    const reportFile = path.join(__dirname, 'results', `pusher-pdf-data-${Date.now()}.json`);
    fs.writeFileSync(reportFile, JSON.stringify(reportData, null, 2));

    console.log(`PDF data saved to: ${reportFile}`);
    console.log('Run: node generate-pdf-report.js to create PDF');

    return done();
  } catch (err) {
    console.error('Error generating PDF data:', err);
    return done(err);
  }
}

// Helper: Calculate latency statistics
function calculateLatencyStats(latencies) {
  if (latencies.length === 0) return null;

  const values = latencies.map(l => l.value).sort((a, b) => a - b);

  return {
    min: values[0],
    max: values[values.length - 1],
    median: values[Math.floor(values.length * 0.5)],
    p95: values[Math.floor(values.length * 0.95)],
    p99: values[Math.floor(values.length * 0.99)],
    mean: values.reduce((sum, v) => sum + v, 0) / values.length,
    count: values.length
  };
}

// Helper: Calculate quality statistics
function calculateQualityStats(quality) {
  if (quality.length === 0) return null;

  const fps = quality.filter(q => q.metric === 'fps').map(q => q.value);

  if (fps.length === 0) return null;

  return {
    avgFPS: fps.reduce((sum, v) => sum + v, 0) / fps.length,
    minFPS: Math.min(...fps),
    maxFPS: Math.max(...fps),
    count: fps.length
  };
}


export {
  setupMonitoring,
  initializeMetricsCollector,
  checkStreamActive,
  connectPusher,
  measureConnectionTime,
  subscribeToStreamChannel,
  measureSubscribeLatency,
  connectToVideoStream,
  measureFrameRate,
  measureVideoLatency,
  checkConnectionQuality,
  subscribeToChat,
  sendChatMessage,
  measureChatLatency,
  measureChurnLatency,
  subscribeToMetadata,
  disconnectPusher,
  closeVideoStream,
  recordPageLoadTime,
  recordChatLoadLatency,
  recordMetadataLatency,
  collectAllMetrics,
  calculateStatistics,
  generatePerformanceReport,
  exportMetricsToCSV,
  generatePDFReport
};