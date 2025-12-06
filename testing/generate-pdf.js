const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

async function generatePDF(htmlPath, pdfPath) {
    console.log('🚀 Launching browser...');

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    try {
        const page = await browser.newPage();

        console.log('📄 Loading HTML file...');
        const htmlContent = fs.readFileSync(htmlPath, 'utf8');
        await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

        console.log('📝 Generating PDF...');
        await page.pdf({
            path: pdfPath,
            format: 'A4',
            margin: {
                top: '20mm',
                bottom: '20mm',
                left: '15mm',
                right: '15mm'
            },
            printBackground: true
        });

        console.log('✅ PDF generated successfully!');
        console.log(`📁 Location: ${pdfPath}`);

    } catch (error) {
        console.error('❌ Error generating PDF:', error.message);
        throw error;
    } finally {
        await browser.close();
    }
}

// Get command line arguments
const args = process.argv.slice(2);

if (args.length < 2) {
    console.error('Usage: node generate-pdf.js <html-file> <pdf-file>');
    process.exit(1);
}

const htmlPath = path.resolve(args[0]);
const pdfPath = path.resolve(args[1]);

// Check if HTML file exists
if (!fs.existsSync(htmlPath)) {
    console.error(`❌ HTML file not found: ${htmlPath}`);
    process.exit(1);
}

// Generate PDF
generatePDF(htmlPath, pdfPath)
    .then(() => {
        console.log('🎉 Done!');
        process.exit(0);
    })
    .catch((error) => {
        console.error('Failed to generate PDF:', error);
        process.exit(1);
    });
