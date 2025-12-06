#!/usr/bin/env node

/**
 * Populate HTML Report with Artillery Test Data
 * This script reads report.json and CSV files, then generates a complete HTML report
 */

const fs = require('fs');
const path = require('path');

// Get result directory from command line or use latest
const resultDir = process.argv[2];

if (!resultDir || !fs.existsSync(resultDir)) {
    console.error('❌ Error: Result directory not found');
    console.error('Usage: node populate-html-report.js <result-directory>');
    process.exit(1);
}

console.log('📊 Populating HTML report with test data...');
console.log(`📁 Result directory: ${resultDir}`);

// Read report.json
const reportJsonPath = path.join(resultDir, 'report.json');
if (!fs.existsSync(reportJsonPath)) {
    console.error('❌ Error: report.json not found');
    process.exit(1);
}

const reportData = JSON.parse(fs.readFileSync(reportJsonPath, 'utf8'));
const aggregate = reportData.aggregate || {};
const counters = aggregate.counters || {};
const summaries = aggregate.summaries || {};

// Read system metrics CSV
const systemMetricsPath = path.join(resultDir, 'system_metrics.csv');
let systemMetrics = { avgCpu: 0, peakCpu: 0, avgMemory: 0, peakMemory: 0 };

if (fs.existsSync(systemMetricsPath)) {
    const csvData = fs.readFileSync(systemMetricsPath, 'utf8');
    const lines = csvData.split('\n').filter(line => line.trim() && !line.startsWith('timestamp'));

    let cpuSum = 0, memSum = 0, maxCpu = 0, maxMem = 0;
    lines.forEach(line => {
        const [, cpu, mem] = line.split(',').map(v => parseFloat(v));
        if (!isNaN(cpu) && !isNaN(mem)) {
            cpuSum += cpu;
            memSum += mem;
            if (cpu > maxCpu) maxCpu = cpu;
            if (mem > maxMem) maxMem = mem;
        }
    });

    systemMetrics = {
        avgCpu: (cpuSum / lines.length).toFixed(2),
        peakCpu: maxCpu.toFixed(2),
        avgMemory: (memSum / lines.length).toFixed(2),
        peakMemory: maxMem.toFixed(2)
    };
}

// Calculate metrics
const totalRequests = counters['http.requests'] || 0;
const successfulRequests = counters['http.codes.200'] || 0;
const totalErrors = counters['total_errors'] || counters['http_errors'] || 0;
const errorRate = totalRequests > 0 ? ((totalErrors / totalRequests) * 100).toFixed(2) : 0;

const httpLatency = summaries['http.response_time'] || {};
const wsLatency = summaries['websocket_connection_latency'] || summaries['websocket.connection_time'] || summaries['pusher_connection_time'] || {};

const throughput = aggregate.rates?.['http.request_rate'] || 0;
const concurrentConnections = counters['pusher_connections_success'] || counters['websocket.connections'] || 0;

// Generate HTML with embedded data
const htmlTemplate = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Test Report - Live Streaming Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 1.2em;
        }
        
        .meta-info {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .meta-info h3 {
            color: #34495e;
            margin-bottom: 15px;
        }
        
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .meta-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .meta-item label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 0.9em;
            display: block;
            margin-bottom: 5px;
        }
        
        .meta-item .value {
            color: #2c3e50;
            font-size: 1.1em;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #2c3e50;
            font-size: 1.8em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .metric-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .metric-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .metric-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .metric-card h3 {
            font-size: 1em;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .metric-card .value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .metric-card .label {
            font-size: 0.9em;
            opacity: 0.8;
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        table thead {
            background: #34495e;
            color: white;
        }
        
        table th,
        table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 20px;
            }
            
            .metric-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎥 Live Streaming Platform</h1>
            <div class="subtitle">Performance Test Report</div>
        </div>
        
        <div class="meta-info">
            <h3>📋 Test Information</h3>
            <div class="meta-grid">
                <div class="meta-item">
                    <label>Test Date</label>
                    <div class="value">${new Date(aggregate.firstMetricAt || Date.now()).toLocaleString()}</div>
                </div>
                <div class="meta-item">
                    <label>Target URL</label>
                    <div class="value">https://pusher.muncak.id</div>
                </div>
                <div class="meta-item">
                    <label>Total Requests</label>
                    <div class="value">${totalRequests.toLocaleString()}</div>
                </div>
                <div class="meta-item">
                    <label>Test Duration</label>
                    <div class="value">${((aggregate.lastMetricAt - aggregate.firstMetricAt) / 1000 / 60).toFixed(1)} minutes</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>📊 Key Performance Metrics</h2>
            <div class="metrics-grid">
                <div class="metric-card success">
                    <h3>⚡ Latency (Median)</h3>
                    <div class="value">${httpLatency.median || httpLatency.p50 || 0}</div>
                    <div class="label">milliseconds</div>
                </div>
                
                <div class="metric-card info">
                    <h3>📈 Throughput</h3>
                    <div class="value">${throughput}</div>
                    <div class="label">requests/sec</div>
                </div>
                
                <div class="metric-card">
                    <h3>💻 CPU Usage (Avg)</h3>
                    <div class="value">${systemMetrics.avgCpu}%</div>
                    <div class="label">average</div>
                </div>
                
                <div class="metric-card">
                    <h3>🧠 Memory Usage (Avg)</h3>
                    <div class="value">${systemMetrics.avgMemory}</div>
                    <div class="label">MB</div>
                </div>
                
                <div class="metric-card info">
                    <h3>🔌 Concurrent Connections</h3>
                    <div class="value">${concurrentConnections}</div>
                    <div class="label">connections</div>
                </div>
                
                <div class="metric-card ${errorRate > 10 ? 'warning' : 'success'}">
                    <h3>❌ Error Rate</h3>
                    <div class="value">${errorRate}%</div>
                    <div class="label">${totalErrors} errors</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>📈 Research Parameters</h2>
            
            <h3 style="margin-top: 30px; margin-bottom: 15px;">1. Latency Metrics</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Minimum</th>
                            <th>Median (p50)</th>
                            <th>p95</th>
                            <th>p99</th>
                            <th>Maximum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>HTTP Request Latency</td>
                            <td>${httpLatency.min || 0} ms</td>
                            <td>${httpLatency.median || httpLatency.p50 || 0} ms</td>
                            <td>${httpLatency.p95 || 0} ms</td>
                            <td>${httpLatency.p99 || 0} ms</td>
                            <td>${httpLatency.max || 0} ms</td>
                        </tr>
                        <tr>
                            <td>WebSocket Connection</td>
                            <td>${wsLatency.min || 'N/A'}</td>
                            <td>${wsLatency.median || wsLatency.p50 || 'N/A'}</td>
                            <td>${wsLatency.p95 || 'N/A'}</td>
                            <td>${wsLatency.p99 || 'N/A'}</td>
                            <td>${wsLatency.max || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h3 style="margin-top: 30px; margin-bottom: 15px;">2. System Resource Usage</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Resource</th>
                            <th>Average</th>
                            <th>Peak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CPU Usage</td>
                            <td>${systemMetrics.avgCpu}%</td>
                            <td>${systemMetrics.peakCpu}%</td>
                            <td><span class="status-badge ${systemMetrics.peakCpu > 80 ? 'warning' : 'success'}">${systemMetrics.peakCpu > 80 ? 'High' : 'Normal'}</span></td>
                        </tr>
                        <tr>
                            <td>Memory Usage</td>
                            <td>${systemMetrics.avgMemory} MB</td>
                            <td>${systemMetrics.peakMemory} MB</td>
                            <td><span class="status-badge success">Normal</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <h3 style="margin-top: 30px; margin-bottom: 15px;">3. Request Summary</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Requests</td>
                            <td>${totalRequests.toLocaleString()}</td>
                            <td>100%</td>
                        </tr>
                        <tr>
                            <td>Successful (200)</td>
                            <td>${successfulRequests.toLocaleString()}</td>
                            <td>${((successfulRequests / totalRequests) * 100).toFixed(2)}%</td>
                        </tr>
                        <tr>
                            <td>Errors</td>
                            <td>${totalErrors.toLocaleString()}</td>
                            <td>${errorRate}%</td>
                        </tr>
                        <tr>
                            <td>Virtual Users Created</td>
                            <td>${(counters['vusers.created'] || 0).toLocaleString()}</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Virtual Users Completed</td>
                            <td>${(counters['vusers.completed'] || 0).toLocaleString()}</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="section">
            <h2>📝 Test Summary</h2>
            <div class="meta-info">
                <h3>Test Phases</h3>
                <ol style="margin-left: 20px; margin-top: 15px;">
                    <li><strong>Warm-up</strong> (30s): 2 users/sec - Baseline establishment</li>
                    <li><strong>Light Load</strong> (60s): 5-10 users/sec - Normal viewing</li>
                    <li><strong>Medium Load</strong> (90s): 10-25 users/sec - Peak hours simulation</li>
                    <li><strong>Heavy Load</strong> (120s): 25-50 users/sec - Stress testing</li>
                    <li><strong>Spike Test</strong> (60s): 75 users/sec - Sudden traffic surge</li>
                    <li><strong>Cool-down</strong> (30s): 5 users/sec - Recovery testing</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>Generated on ${new Date().toLocaleString()}</p>
            <p>Artillery Performance Testing Suite v1.0</p>
        </div>
    </div>
</body>
</html>`;

// Write the populated HTML
const outputPath = path.join(resultDir, 'performance-report.html');
fs.writeFileSync(outputPath, htmlTemplate);

console.log('✅ HTML report populated successfully!');
console.log(`📄 Output: ${outputPath}`);
console.log('');
console.log('📊 Summary:');
console.log(`  Total Requests: ${totalRequests.toLocaleString()}`);
console.log(`  Successful: ${successfulRequests.toLocaleString()} (${((successfulRequests / totalRequests) * 100).toFixed(2)}%)`);
console.log(`  Errors: ${totalErrors.toLocaleString()} (${errorRate}%)`);
console.log(`  Median Latency: ${httpLatency.median || httpLatency.p50 || 0} ms`);
console.log(`  Throughput: ${throughput} req/s`);
console.log(`  CPU (Avg/Peak): ${systemMetrics.avgCpu}% / ${systemMetrics.peakCpu}%`);
console.log(`  Memory (Avg/Peak): ${systemMetrics.avgMemory} MB / ${systemMetrics.peakMemory} MB`);
