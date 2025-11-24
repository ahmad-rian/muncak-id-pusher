// Enhanced Artillery processor - ALL RESEARCH PARAMETERS
// Focus: Pusher signaling + System metrics + Video quality

import Pusher from 'pusher-js';
import fs from 'fs';
import path from 'path';
import os from 'os';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Comprehensive metrics collector
const metrics = {
    // Pusher signaling
    chatMessages: [],
    viewerCountUpdates: [],
    mirrorStateChanges: [],
    connectionTimes: [],
    errors: [],

    // System metrics (NEW)
    cpuUsage: [],
    memoryUsage: [],

    // Video quality (NEW)
    videoQuality: [],
    qualityChanges: [],

    startTime: Date.now()
};

// System monitoring interval
let systemMonitor = null;

// Start system monitoring
function startSystemMonitoring(context, events, done) {
    console.log('📊 Starting system monitoring...');

    // Monitor every 5 seconds
    systemMonitor = setInterval(() => {
        // CPU Usage
        const cpus = os.cpus();
        let totalIdle = 0;
        let totalTick = 0;

        cpus.forEach(cpu => {
            for (let type in cpu.times) {
                totalTick += cpu.times[type];
            }
            totalIdle += cpu.times.idle;
        });

        const cpuUsagePercent = 100 - ~~(100 * totalIdle / totalTick);

        // Memory Usage
        const totalMem = os.totalmem();
        const freeMem = os.freemem();
        const usedMem = totalMem - freeMem;
        const memUsagePercent = (usedMem / totalMem) * 100;
        const memUsageMB = usedMem / 1024 / 1024;

        // Record metrics
        metrics.cpuUsage.push({
            timestamp: Date.now(),
            percent: cpuUsagePercent
        });

        metrics.memoryUsage.push({
            timestamp: Date.now(),
            percent: memUsagePercent,
            usedMB: memUsageMB,
            totalMB: totalMem / 1024 / 1024
        });

        // Emit to Artillery
        events.emit('histogram', 'system_cpu_usage', cpuUsagePercent);
        events.emit('histogram', 'system_memory_usage_percent', memUsagePercent);
        events.emit('histogram', 'system_memory_usage_mb', memUsageMB);

    }, 5000); // Every 5 seconds

    return done();
}

// Stop system monitoring
function stopSystemMonitoring(context, events, done) {
    if (systemMonitor) {
        clearInterval(systemMonitor);
        console.log('📊 System monitoring stopped');
    }
    return done();
}

// Random stream slug
function getRandomStreamSlug(context, events, done) {
    const slugs = [
        'cum-cillum-laborum-voluptate-alias-gJZ2K6',
        'test-stream-1',
        'test-stream-2'
    ];
    context.vars.randomStreamSlug = slugs[Math.floor(Math.random() * slugs.length)];
    return done();
}

// Subscribe to Pusher (with enhanced metrics)
function subscribeToPusherChannel(context, events, done) {
    const startTime = Date.now();

    try {
        const pusher = new Pusher(context.vars.pusher_key, {
            cluster: context.vars.pusher_cluster,
            forceTLS: true
        });

        const streamId = context.vars.streamId || '8';
        const channel = pusher.subscribe(`stream.${streamId}`);

        channel.bind('pusher:subscription_succeeded', () => {
            const latency = Date.now() - startTime;

            // METRIC 6: Connection Establishment Time
            events.emit('histogram', 'connection_establishment_time', latency);
            events.emit('counter', 'pusher_connections_success', 1);

            // METRIC 5: Concurrent Connections (tracked)
            metrics.connectionTimes.push({
                timestamp: Date.now(),
                latency: latency
            });

            context.vars.pusherChannel = channel;
            context.vars.pusher = pusher;

            // Listen for chat messages
            channel.bind('App\\Events\\ChatMessageSent', (data) => {
                const messageLatency = Date.now() - (data.timestamp || Date.now());

                // METRIC 1: Latency (Chat message)
                events.emit('histogram', 'chat_message_latency', messageLatency);
                events.emit('counter', 'chat_message_count', 1);

                metrics.chatMessages.push({
                    timestamp: Date.now(),
                    latency: messageLatency,
                    from: data.username
                });
            });

            // Listen for viewer count updates
            channel.bind('App\\Events\\ViewerCountUpdated', (data) => {
                events.emit('counter', 'viewer_count_updates', 1);
                events.emit('gauge', 'current_viewer_count', data.count);

                metrics.viewerCountUpdates.push({
                    timestamp: Date.now(),
                    count: data.count
                });
            });

            // Listen for mirror state changes
            channel.bind('App\\Events\\MirrorStateChanged', (data) => {
                events.emit('counter', 'mirror_state_changes', 1);

                metrics.mirrorStateChanges.push({
                    timestamp: Date.now(),
                    is_mirrored: data.is_mirrored
                });
            });

            // Listen for quality changes (NEW)
            channel.bind('App\\Events\\QualityChanged', (data) => {
                // METRIC 8: Video Quality Stability
                events.emit('counter', 'quality_changes', 1);
                events.emit('gauge', 'current_quality', data.quality === '1080p' ? 1080 : 720);

                metrics.qualityChanges.push({
                    timestamp: Date.now(),
                    quality: data.quality,
                    reason: data.reason || 'manual'
                });
            });

            done();
        });

        channel.bind('pusher:subscription_error', (err) => {
            // METRIC 7: Error Rate
            events.emit('counter', 'pusher_connections_failed', 1);
            events.emit('counter', 'total_errors', 1);

            metrics.errors.push({
                timestamp: Date.now(),
                type: 'subscription',
                error: err.message
            });

            done(err);
        });

    } catch (err) {
        events.emit('counter', 'pusher_errors', 1);
        events.emit('counter', 'total_errors', 1);
        done(err);
    }
}

// Track video quality metrics
function trackVideoQuality(context, events, done) {
    // Simulate video quality tracking
    // In production, this would come from LiveKit client
    const quality = context.vars.currentQuality || '720p';
    const qualityValue = quality === '1080p' ? 1080 : 720;

    // METRIC 8: Video Quality Stability
    metrics.videoQuality.push({
        timestamp: Date.now(),
        quality: quality,
        stable: true // Would be calculated from actual stream data
    });

    events.emit('gauge', 'video_quality', qualityValue);
    events.emit('counter', 'video_quality_samples', 1);

    return done();
}

// Calculate throughput
function calculateThroughput(context, events, done) {
    const duration = (Date.now() - metrics.startTime) / 1000; // seconds

    if (duration > 0) {
        // METRIC 2: Throughput
        const chatThroughput = metrics.chatMessages.length / duration;
        const viewerUpdateThroughput = metrics.viewerCountUpdates.length / duration;

        events.emit('histogram', 'chat_throughput_msgs_per_sec', chatThroughput);
        events.emit('histogram', 'viewer_update_throughput_per_sec', viewerUpdateThroughput);
    }

    return done();
}

// Disconnect from Pusher
function disconnectPusher(context, events, done) {
    if (context.vars.pusher) {
        context.vars.pusher.disconnect();
        events.emit('counter', 'pusher_disconnections', 1);
    }
    return done();
}

// Generate comprehensive report
function generateComprehensiveReport(context, events, done) {
    const duration = Date.now() - metrics.startTime;

    // Calculate statistics
    const avgCPU = metrics.cpuUsage.length > 0
        ? metrics.cpuUsage.reduce((sum, c) => sum + c.percent, 0) / metrics.cpuUsage.length
        : 0;

    const avgMemory = metrics.memoryUsage.length > 0
        ? metrics.memoryUsage.reduce((sum, m) => sum + m.percent, 0) / metrics.memoryUsage.length
        : 0;

    const avgChatLatency = metrics.chatMessages.length > 0
        ? metrics.chatMessages.reduce((sum, m) => sum + m.latency, 0) / metrics.chatMessages.length
        : 0;

    const avgConnectionTime = metrics.connectionTimes.length > 0
        ? metrics.connectionTimes.reduce((sum, c) => sum + c.latency, 0) / metrics.connectionTimes.length
        : 0;

    const report = {
        test_type: 'Comprehensive Performance Test - Pusher Signaling',
        duration_ms: duration,
        timestamp: new Date().toISOString(),

        // ALL 8 PARAMETERS
        parameters: {
            // 1. Latency
            latency: {
                chat_avg_ms: avgChatLatency,
                chat_min_ms: Math.min(...metrics.chatMessages.map(m => m.latency)),
                chat_max_ms: Math.max(...metrics.chatMessages.map(m => m.latency)),
                samples: metrics.chatMessages.length
            },

            // 2. Throughput
            throughput: {
                chat_msgs_per_sec: metrics.chatMessages.length / (duration / 1000),
                viewer_updates_per_sec: metrics.viewerCountUpdates.length / (duration / 1000),
                total_events_per_sec: (metrics.chatMessages.length + metrics.viewerCountUpdates.length) / (duration / 1000)
            },

            // 3. CPU Usage
            cpu_usage: {
                avg_percent: avgCPU,
                min_percent: Math.min(...metrics.cpuUsage.map(c => c.percent)),
                max_percent: Math.max(...metrics.cpuUsage.map(c => c.percent)),
                samples: metrics.cpuUsage.length
            },

            // 4. Memory Usage
            memory_usage: {
                avg_percent: avgMemory,
                avg_mb: metrics.memoryUsage.length > 0
                    ? metrics.memoryUsage.reduce((sum, m) => sum + m.usedMB, 0) / metrics.memoryUsage.length
                    : 0,
                max_mb: Math.max(...metrics.memoryUsage.map(m => m.usedMB)),
                samples: metrics.memoryUsage.length
            },

            // 5. Concurrent Connections
            concurrent_connections: {
                total: metrics.connectionTimes.length,
                successful: metrics.connectionTimes.length - metrics.errors.filter(e => e.type === 'subscription').length,
                failed: metrics.errors.filter(e => e.type === 'subscription').length
            },

            // 6. Connection Establishment Time
            connection_establishment: {
                avg_ms: avgConnectionTime,
                min_ms: Math.min(...metrics.connectionTimes.map(c => c.latency)),
                max_ms: Math.max(...metrics.connectionTimes.map(c => c.latency)),
                samples: metrics.connectionTimes.length
            },

            // 7. Error Rate
            error_rate: {
                total_errors: metrics.errors.length,
                error_percentage: (metrics.errors.length / metrics.connectionTimes.length) * 100,
                by_type: {
                    subscription: metrics.errors.filter(e => e.type === 'subscription').length,
                    other: metrics.errors.filter(e => e.type !== 'subscription').length
                }
            },

            // 8. Video Quality Stability
            video_quality: {
                quality_changes: metrics.qualityChanges.length,
                stability_score: 100 - (metrics.qualityChanges.length / (duration / 1000) * 10), // Changes per second penalty
                qualities_used: {
                    '720p': metrics.qualityChanges.filter(q => q.quality === '720p').length,
                    '1080p': metrics.qualityChanges.filter(q => q.quality === '1080p').length
                }
            }
        },

        // Raw data (limited)
        raw_data: {
            cpu_samples: metrics.cpuUsage.slice(-10),
            memory_samples: metrics.memoryUsage.slice(-10),
            chat_samples: metrics.chatMessages.slice(0, 50),
            connection_samples: metrics.connectionTimes.slice(0, 50),
            error_samples: metrics.errors
        }
    };

    // Save report
    const resultsDir = path.join(__dirname, 'results');
    if (!fs.existsSync(resultsDir)) {
        fs.mkdirSync(resultsDir, { recursive: true });
    }

    const reportFile = path.join(resultsDir, `comprehensive-report-${Date.now()}.json`);
    fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));

    console.log('\n========== COMPREHENSIVE PERFORMANCE REPORT ==========');
    console.log(`Duration: ${(duration / 1000).toFixed(2)}s`);
    console.log('\n1. LATENCY:');
    console.log(`   Chat Avg: ${report.parameters.latency.chat_avg_ms.toFixed(2)}ms`);
    console.log('\n2. THROUGHPUT:');
    console.log(`   Chat: ${report.parameters.throughput.chat_msgs_per_sec.toFixed(2)} msg/s`);
    console.log('\n3. CPU USAGE:');
    console.log(`   Avg: ${report.parameters.cpu_usage.avg_percent.toFixed(2)}%`);
    console.log('\n4. MEMORY USAGE:');
    console.log(`   Avg: ${report.parameters.memory_usage.avg_percent.toFixed(2)}%`);
    console.log(`   Avg MB: ${report.parameters.memory_usage.avg_mb.toFixed(2)} MB`);
    console.log('\n5. CONCURRENT CONNECTIONS:');
    console.log(`   Total: ${report.parameters.concurrent_connections.total}`);
    console.log(`   Successful: ${report.parameters.concurrent_connections.successful}`);
    console.log('\n6. CONNECTION ESTABLISHMENT:');
    console.log(`   Avg: ${report.parameters.connection_establishment.avg_ms.toFixed(2)}ms`);
    console.log('\n7. ERROR RATE:');
    console.log(`   Total Errors: ${report.parameters.error_rate.total_errors}`);
    console.log(`   Error Rate: ${report.parameters.error_rate.error_percentage.toFixed(2)}%`);
    console.log('\n8. VIDEO QUALITY STABILITY:');
    console.log(`   Quality Changes: ${report.parameters.video_quality.quality_changes}`);
    console.log(`   Stability Score: ${report.parameters.video_quality.stability_score.toFixed(2)}/100`);
    console.log('\n======================================================');
    console.log(`Report saved: ${reportFile}\n`);

    return done();
}

export {
    startSystemMonitoring,
    stopSystemMonitoring,
    getRandomStreamSlug,
    subscribeToPusherChannel,
    trackVideoQuality,
    calculateThroughput,
    disconnectPusher,
    generateComprehensiveReport
};
