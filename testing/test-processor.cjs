const Pusher = require('pusher-js');

// Global tracking for metrics
const connectionMetrics = {
    activeConnections: 0,
    totalConnections: 0,
    totalErrors: 0
};

// Track request start time for latency measurement
function recordRequestStart(requestParams, context, ee, next) {
    context.vars.requestStartTime = Date.now();
    return next();
}

// Record response metrics (latency, throughput)
function recordResponseMetrics(requestParams, response, context, ee, next) {
    if (context.vars.requestStartTime) {
        const latency = Date.now() - context.vars.requestStartTime;

        // Emit latency metric
        ee.emit('histogram', 'http_request_latency', latency);

        // Track errors
        if (response.statusCode >= 400) {
            ee.emit('counter', 'http_errors', 1);
            ee.emit('counter', 'total_errors', 1);
            connectionMetrics.totalErrors++;
        }

        // Track throughput (requests per second is automatic in Artillery)
        ee.emit('counter', 'requests_completed', 1);
    }

    return next();
}

// Subscribe to Pusher channel and measure connection time
function subscribeToPusherChannel(context, events, done) {
    const startTime = Date.now();

    try {
        const pusher = new Pusher(context.vars.pusher_key, {
            cluster: context.vars.pusher_cluster,
            forceTLS: true,
            enabledTransports: ['ws', 'wss']
        });

        const streamId = context.vars.streamId || '8';
        const channelName = `stream.${streamId}`;
        const channel = pusher.subscribe(channelName);

        // Track connection attempt
        connectionMetrics.totalConnections++;

        channel.bind('pusher:subscription_succeeded', () => {
            const connectionTime = Date.now() - startTime;

            // Track connection establishment time
            events.emit('histogram', 'connection_establishment_time', connectionTime);
            events.emit('histogram', 'websocket_connection_latency', connectionTime);
            events.emit('counter', 'pusher_connections_success', 1);

            // Track active connections
            connectionMetrics.activeConnections++;
            events.emit('gauge', 'concurrent_connections', connectionMetrics.activeConnections);
            events.emit('gauge', 'active_websocket_connections', connectionMetrics.activeConnections);

            // Store connection info
            context.vars.pusherChannel = channel;
            context.vars.pusher = pusher;
            context.vars.pusherConnectedAt = Date.now();

            // Listen for chat messages
            channel.bind('App\\\\Events\\\\ChatMessageSent', (data) => {
                const messageLatency = Date.now() - (data.timestamp || Date.now());
                events.emit('histogram', 'chat_message_latency', messageLatency);
                events.emit('counter', 'chat_messages_received', 1);
                events.emit('counter', 'messages_per_second', 1);
            });

            // Listen for viewer count updates
            channel.bind('App\\\\Events\\\\ViewerCountUpdated', (data) => {
                events.emit('counter', 'viewer_count_updates', 1);
                events.emit('gauge', 'current_viewer_count', data.count || 0);
            });

            // Listen for quality changes
            channel.bind('App\\\\Events\\\\QualityChanged', (data) => {
                events.emit('counter', 'video_quality_changes', 1);

                const qualityValue = data.quality === '1080p' ? 1080 : 720;
                events.emit('gauge', 'current_video_quality', qualityValue);

                // Track quality stability
                if (data.quality === '720p') {
                    events.emit('counter', 'video_quality_720p_stable', 1);
                } else if (data.quality === '1080p') {
                    events.emit('counter', 'video_quality_1080p_stable', 1);
                }
            });

            // Listen for chunk events (video streaming)
            channel.bind('App\\\\Events\\\\NewChunkAvailable', (data) => {
                events.emit('counter', 'video_chunks_received', 1);

                // Track quality from chunk data
                if (data.quality) {
                    const qualityValue = data.quality === '1080p' ? 1080 : 720;
                    events.emit('gauge', 'current_video_quality', qualityValue);
                }
            });

            done();
        });

        channel.bind('pusher:subscription_error', (err) => {
            events.emit('counter', 'pusher_connections_failed', 1);
            events.emit('counter', 'websocket_errors', 1);
            events.emit('counter', 'total_errors', 1);
            connectionMetrics.totalErrors++;
            done(err);
        });

        // Timeout after 30 seconds
        setTimeout(() => {
            if (!context.vars.pusher) {
                events.emit('counter', 'pusher_connections_failed', 1);
                events.emit('counter', 'websocket_errors', 1);
                done(new Error('Pusher connection timeout'));
            }
        }, 30000);

    } catch (err) {
        events.emit('counter', 'pusher_connections_failed', 1);
        events.emit('counter', 'websocket_errors', 1);
        events.emit('counter', 'total_errors', 1);
        connectionMetrics.totalErrors++;
        done(err);
    }
}

// Simulate video quality at 720p
function simulateVideoQuality720p(context, events, done) {
    try {
        // Simulate quality check
        const qualityCheckTime = Date.now() - (context.vars.pusherConnectedAt || Date.now());

        // Record 720p quality stability
        events.emit('counter', 'video_quality_720p_stable', 1);
        events.emit('gauge', 'current_video_quality', 720);

        // Simulate quality check latency (5-15ms)
        const simulatedLatency = Math.floor(Math.random() * 10) + 5;
        events.emit('histogram', 'video_quality_check_latency', simulatedLatency);

        context.vars.currentQuality = '720p';

        done();
    } catch (err) {
        done(err);
    }
}

// Simulate video quality at 1080p
function simulateVideoQuality1080p(context, events, done) {
    try {
        // Simulate quality change
        const previousQuality = context.vars.currentQuality || '720p';

        if (previousQuality !== '1080p') {
            events.emit('counter', 'video_quality_changes', 1);
        }

        // Record 1080p quality stability
        events.emit('counter', 'video_quality_1080p_stable', 1);
        events.emit('gauge', 'current_video_quality', 1080);

        // Simulate quality check latency (5-15ms)
        const simulatedLatency = Math.floor(Math.random() * 10) + 5;
        events.emit('histogram', 'video_quality_check_latency', simulatedLatency);

        context.vars.currentQuality = '1080p';

        done();
    } catch (err) {
        done(err);
    }
}

// Disconnect from Pusher cleanly
function disconnectPusher(context, events, done) {
    try {
        // Calculate connection duration
        if (context.vars.pusherConnectedAt) {
            const connectionDuration = Date.now() - context.vars.pusherConnectedAt;
            events.emit('histogram', 'connection_duration', connectionDuration);
        }

        // Unsubscribe from channel
        if (context.vars.pusherChannel) {
            context.vars.pusherChannel.unbind_all();
            context.vars.pusher.unsubscribe(context.vars.pusherChannel.name);
        }

        // Disconnect
        if (context.vars.pusher) {
            context.vars.pusher.disconnect();
            events.emit('counter', 'pusher_disconnections', 1);

            // Update active connections count
            connectionMetrics.activeConnections--;
            events.emit('gauge', 'concurrent_connections', Math.max(0, connectionMetrics.activeConnections));
            events.emit('gauge', 'active_websocket_connections', Math.max(0, connectionMetrics.activeConnections));
        }

        // Clean up context
        delete context.vars.pusher;
        delete context.vars.pusherChannel;
        delete context.vars.pusherConnectedAt;

    } catch (err) {
        // Ignore disconnect errors but still update metrics
        if (connectionMetrics.activeConnections > 0) {
            connectionMetrics.activeConnections--;
        }
        events.emit('gauge', 'concurrent_connections', Math.max(0, connectionMetrics.activeConnections));
    }

    // Always call done
    done();
}

// Calculate error rate
function calculateErrorRate(context, events, done) {
    if (connectionMetrics.totalConnections > 0) {
        const errorRate = (connectionMetrics.totalErrors / connectionMetrics.totalConnections) * 100;
        events.emit('gauge', 'error_rate_percentage', errorRate);
    }
    done();
}

module.exports = {
    recordRequestStart,
    recordResponseMetrics,
    subscribeToPusherChannel,
    simulateVideoQuality720p,
    simulateVideoQuality1080p,
    disconnectPusher,
    calculateErrorRate
};
