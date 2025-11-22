// generate-pdf-report.js
// Script untuk generate PDF report dari hasil testing Artillery

import PDFDocument from 'pdfkit';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Fungsi untuk format angka
function formatNumber(num) {
    return num.toFixed(2);
}

// Fungsi untuk format durasi
function formatDuration(ms) {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);

    if (hours > 0) {
        return `${hours}h ${minutes % 60}m ${seconds % 60}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${seconds % 60}s`;
    } else {
        return `${seconds}s`;
    }
}

// Fungsi utama untuk generate PDF
function generatePDFReport(jsonDataFile, outputFile) {
    console.log(`Reading data from: ${jsonDataFile}`);

    // Baca data JSON
    const data = JSON.parse(fs.readFileSync(jsonDataFile, 'utf8'));

    // Create PDF document
    const doc = new PDFDocument({
        size: 'A4',
        margins: { top: 50, bottom: 50, left: 50, right: 50 }
    });

    // Pipe PDF ke file
    doc.pipe(fs.createWriteStream(outputFile));

    // ========== COVER PAGE ==========
    doc.fontSize(28)
        .font('Helvetica-Bold')
        .text('Performance Test Report', { align: 'center' });

    doc.moveDown(0.5);

    doc.fontSize(20)
        .font('Helvetica')
        .text('WebSocket Live Cam Streaming', { align: 'center' });

    doc.moveDown(2);

    // Logo/Banner placeholder
    doc.fontSize(14)
        .font('Helvetica-Bold')
        .text('PUSHER WEBSOCKET', { align: 'center' });

    doc.moveDown(3);

    // Info dasar
    doc.fontSize(12)
        .font('Helvetica')
        .text(`Stream: ${data.stream}`, { align: 'center' });

    doc.moveDown(0.5);

    doc.text(`Test Date: ${new Date(data.timestamp).toLocaleString('id-ID')}`, { align: 'center' });

    doc.moveDown(0.5);

    doc.text(`Duration: ${formatDuration(data.duration)}`, { align: 'center' });

    // Footer cover
    doc.moveDown(8);
    doc.fontSize(10)
        .text('Analisis Perbandingan Performa WebSocket', { align: 'center' });
    doc.text('Menggunakan Pusher dan Laravel Reverb', { align: 'center' });
    doc.text('Website muncak.id', { align: 'center' });

    // ========== PAGE 2: EXECUTIVE SUMMARY ==========
    doc.addPage();

    doc.fontSize(20)
        .font('Helvetica-Bold')
        .text('Executive Summary', { underline: true });

    doc.moveDown();

    doc.fontSize(12)
        .font('Helvetica');

    // Summary box
    const summaryY = doc.y;
    doc.rect(50, summaryY, 495, 150)
        .fillAndStroke('#f0f0f0', '#333333')
        .fillColor('#000000');

    doc.y = summaryY + 15;

    doc.fontSize(14)
        .font('Helvetica-Bold')
        .text('Test Summary', 70);

    doc.moveDown(0.5);

    doc.fontSize(11)
        .font('Helvetica');

    const summaryData = [
        ['Total Connections:', data.summary.totalConnections],
        ['Successful Connections:', data.summary.successfulConnections],
        ['Failed Connections:', data.summary.errors],
        ['Success Rate:', `${((data.summary.successfulConnections / data.summary.totalConnections) * 100).toFixed(2)}%`],
        ['Average Connection Time:', `${formatNumber(data.summary.avgConnectionTime)} ms`]
    ];

    let yPos = doc.y;
    summaryData.forEach(([label, value]) => {
        doc.text(label, 70, yPos, { continued: true, width: 250 });
        doc.font('Helvetica-Bold')
            .text(value, { align: 'right', width: 200 });
        doc.font('Helvetica');
        yPos += 20;
    });

    doc.moveDown(2);

    // Key Findings
    doc.fontSize(14)
        .font('Helvetica-Bold')
        .text('Key Findings');

    doc.moveDown(0.5);

    doc.fontSize(11)
        .font('Helvetica');

    const findings = [
        `✓ WebSocket established ${data.summary.successfulConnections} successful connections`,
        `✓ Average connection latency: ${formatNumber(data.summary.avgConnectionTime)} ms`,
        data.latencies ? `✓ Median video latency: ${formatNumber(data.latencies.median)} ms` : null,
        data.latencies ? `✓ 95th percentile latency: ${formatNumber(data.latencies.p95)} ms` : null,
        data.quality ? `✓ Average frame rate: ${formatNumber(data.quality.avgFPS)} fps` : null
    ].filter(Boolean);

    findings.forEach(finding => {
        doc.text(finding, { indent: 20 });
        doc.moveDown(0.3);
    });

    // ========== PAGE 3: LATENCY METRICS ==========
    doc.addPage();

    doc.fontSize(20)
        .font('Helvetica-Bold')
        .text('Latency Metrics', { underline: true });

    doc.moveDown();

    if (data.latencies) {
        // Table header
        const tableTop = doc.y;
        const col1 = 50;
        const col2 = 200;
        const col3 = 350;

        doc.fontSize(11)
            .font('Helvetica-Bold');

        doc.text('Metric', col1, tableTop);
        doc.text('Value', col2, tableTop);
        doc.text('Unit', col3, tableTop);

        // Draw header line
        doc.moveTo(col1, tableTop + 15)
            .lineTo(495, tableTop + 15)
            .stroke();

        doc.moveDown();

        // Table data
        doc.font('Helvetica');

        const latencyMetrics = [
            ['Minimum Latency', formatNumber(data.latencies.min), 'ms'],
            ['Maximum Latency', formatNumber(data.latencies.max), 'ms'],
            ['Median Latency (P50)', formatNumber(data.latencies.median), 'ms'],
            ['95th Percentile (P95)', formatNumber(data.latencies.p95), 'ms'],
            ['99th Percentile (P99)', formatNumber(data.latencies.p99), 'ms'],
            ['Average Latency', formatNumber(data.latencies.mean), 'ms'],
            ['Sample Count', data.latencies.count, 'samples']
        ];

        let rowY = doc.y + 5;
        latencyMetrics.forEach(([metric, value, unit]) => {
            doc.text(metric, col1, rowY);
            doc.text(value, col2, rowY);
            doc.text(unit, col3, rowY);
            rowY += 25;
        });

        // Latency Analysis
        doc.moveDown(2);

        doc.fontSize(14)
            .font('Helvetica-Bold')
            .text('Latency Analysis');

        doc.moveDown(0.5);

        doc.fontSize(11)
            .font('Helvetica');

        // Determine performance level
        let performanceLevel = 'Good';
        let performanceColor = 'green';

        if (data.latencies.p95 > 3000) {
            performanceLevel = 'Poor';
            performanceColor = 'red';
        } else if (data.latencies.p95 > 2000) {
            performanceLevel = 'Fair';
            performanceColor = 'orange';
        }

        doc.text(`Performance Level: `, { continued: true });
        doc.fillColor(performanceColor)
            .font('Helvetica-Bold')
            .text(performanceLevel);

        doc.fillColor('#000000')
            .font('Helvetica');

        doc.moveDown(0.5);

        const analysis = [
            `• The median latency of ${formatNumber(data.latencies.median)}ms indicates ${data.latencies.median < 1000 ? 'excellent' : 'acceptable'} response time`,
            `• 95% of requests completed within ${formatNumber(data.latencies.p95)}ms`,
            `• Maximum latency observed: ${formatNumber(data.latencies.max)}ms`
        ];

        analysis.forEach(line => {
            doc.text(line);
            doc.moveDown(0.3);
        });
    } else {
        doc.fontSize(11)
            .font('Helvetica-Oblique')
            .text('No latency data available');
    }

    // ========== PAGE 4: QUALITY METRICS ==========
    doc.addPage();

    doc.fontSize(20)
        .font('Helvetica-Bold')
        .text('Video Quality Metrics', { underline: true });

    doc.moveDown();

    if (data.quality) {
        // Table header
        const tableTop = doc.y;
        const col1 = 50;
        const col2 = 250;
        const col3 = 400;

        doc.fontSize(11)
            .font('Helvetica-Bold');

        doc.text('Metric', col1, tableTop);
        doc.text('Value', col2, tableTop);
        doc.text('Unit', col3, tableTop);

        doc.moveTo(col1, tableTop + 15)
            .lineTo(495, tableTop + 15)
            .stroke();

        doc.moveDown();

        // Table data
        doc.font('Helvetica');

        const qualityMetrics = [
            ['Average Frame Rate', formatNumber(data.quality.avgFPS), 'fps'],
            ['Minimum Frame Rate', formatNumber(data.quality.minFPS), 'fps'],
            ['Maximum Frame Rate', formatNumber(data.quality.maxFPS), 'fps'],
            ['Frame Rate Samples', data.quality.count, 'samples']
        ];

        let rowY = doc.y + 5;
        qualityMetrics.forEach(([metric, value, unit]) => {
            doc.text(metric, col1, rowY);
            doc.text(value, col2, rowY);
            doc.text(unit, col3, rowY);
            rowY += 25;
        });

        // Quality Analysis
        doc.moveDown(2);

        doc.fontSize(14)
            .font('Helvetica-Bold')
            .text('Quality Analysis');

        doc.moveDown(0.5);

        doc.fontSize(11)
            .font('Helvetica');

        const qualityLevel = data.quality.avgFPS >= 30 ? 'Excellent (30+ fps)' :
            data.quality.avgFPS >= 24 ? 'Good (24-30 fps)' :
                'Poor (< 24 fps)';

        doc.text(`Stream Quality: `, { continued: true });
        doc.font('Helvetica-Bold')
            .text(qualityLevel);

        doc.font('Helvetica');
        doc.moveDown(0.5);

        const qualityAnalysis = [
            `• Average frame rate of ${formatNumber(data.quality.avgFPS)} fps provides ${data.quality.avgFPS >= 30 ? 'smooth' : 'acceptable'} viewing experience`,
            `• Frame rate consistency: ${formatNumber(data.quality.maxFPS - data.quality.minFPS)} fps variation`,
            `• Stream maintained ${data.quality.minFPS >= 24 ? 'acceptable' : 'suboptimal'} minimum frame rate`
        ];

        qualityAnalysis.forEach(line => {
            doc.text(line);
            doc.moveDown(0.3);
        });

    } else {
        doc.fontSize(11)
            .font('Helvetica-Oblique')
            .text('No quality data available');
    }

    // ========== PAGE 5: RECOMMENDATIONS ==========
    doc.addPage();

    doc.fontSize(20)
        .font('Helvetica-Bold')
        .text('Recommendations', { underline: true });

    doc.moveDown();

    doc.fontSize(12)
        .font('Helvetica');

    const recommendations = [];

    if (data.latencies) {
        if (data.latencies.p95 > 2000) {
            recommendations.push('⚠ High latency detected (P95 > 2000ms). Consider optimizing server resources or network configuration.');
        }

        if (data.latencies.max > 5000) {
            recommendations.push('⚠ Maximum latency exceeds 5 seconds. Investigate network bottlenecks.');
        }
    }

    if (data.quality) {
        if (data.quality.avgFPS < 24) {
            recommendations.push('⚠ Frame rate below 24 fps. Consider reducing video resolution or bitrate.');
        }

        if (data.quality.maxFPS - data.quality.minFPS > 10) {
            recommendations.push('⚠ High frame rate variation detected. Check for resource contention.');
        }
    }

    if (data.summary.errors > 0) {
        const errorRate = (data.summary.errors / data.summary.totalConnections) * 100;
        if (errorRate > 1) {
            recommendations.push(`⚠ Error rate of ${formatNumber(errorRate)}% exceeds acceptable threshold (1%).`);
        }
    }

    if (recommendations.length === 0) {
        recommendations.push('✓ All metrics within acceptable ranges. System performing well.');
        recommendations.push('✓ Continue monitoring for sustained performance.');
    }

    recommendations.forEach(rec => {
        doc.text(rec);
        doc.moveDown(0.5);
    });

    doc.moveDown();

    // General Recommendations
    doc.fontSize(14)
        .font('Helvetica-Bold')
        .text('General Recommendations');

    doc.moveDown(0.5);

    doc.fontSize(11)
        .font('Helvetica');

    const generalRecs = [
        '1. Monitor system resources (CPU, Memory, Network) during peak loads',
        '2. Implement rate limiting to prevent system overload',
        '3. Use CDN for static assets to reduce server load',
        '4. Enable compression for WebSocket messages',
        '5. Implement automatic reconnection with exponential backoff',
        '6. Monitor real user metrics (RUM) in production',
        '7. Set up alerts for latency and error rate thresholds',
        '8. Regularly review and optimize database queries',
        '9. Consider horizontal scaling for high concurrency scenarios',
        '10. Implement proper caching strategies'
    ];

    generalRecs.forEach(rec => {
        doc.text(rec);
        doc.moveDown(0.3);
    });

    // ========== FOOTER ==========
    doc.fontSize(9)
        .font('Helvetica-Oblique')
        .text(
            `Report generated on ${new Date().toLocaleString('id-ID')}`,
            50,
            doc.page.height - 50,
            { align: 'center' }
        );

    // Finalize PDF
    doc.end();

    console.log(`PDF report generated: ${outputFile}`);
}


// Main execution
if (import.meta.url === `file://${process.argv[1]}`) {
    const args = process.argv.slice(2);

    if (args.length === 0) {
        console.log('Usage: node generate-pdf-report.js <json-data-file> [output-pdf-file]');
        console.log('');
        console.log('Example:');
        console.log('  node generate-pdf-report.js results/pusher-pdf-data-1234567890.json');
        console.log('  node generate-pdf-report.js results/pusher-pdf-data-1234567890.json custom-report.pdf');
        process.exit(1);
    }

    const jsonFile = args[0];
    const outputFile = args[1] || jsonFile.replace('.json', '.pdf').replace('pdf-data', 'report');

    if (!fs.existsSync(jsonFile)) {
        console.error(`Error: File not found: ${jsonFile}`);
        process.exit(1);
    }

    try {
        generatePDFReport(jsonFile, outputFile);
        console.log('✓ PDF report generated successfully!');
    } catch (error) {
        console.error('Error generating PDF:', error);
        process.exit(1);
    }
}

export { generatePDFReport };