#!/bin/bash

# Quick validation test - runs a short test to verify setup

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/.env.artillery"

echo "🔍 Validating Artillery Test Setup..."
echo ""

# Check Artillery installation
if command -v artillery &> /dev/null; then
    echo "✓ Artillery installed: $(artillery --version)"
else
    echo "✗ Artillery not found"
    exit 1
fi

# Check environment file
if [ -f "${ENV_FILE}" ]; then
    echo "✓ Environment file exists"
    source "${ENV_FILE}"
else
    echo "✗ .env.artillery file not found"
    exit 1
fi

# Check dependencies
if [ -d "${SCRIPT_DIR}/node_modules/pusher-js" ]; then
    echo "✓ pusher-js installed"
else
    echo "✗ pusher-js not found - run: npm install"
    exit 1
fi

# Check configuration file
if [ -f "${SCRIPT_DIR}/performance-test.yml" ]; then
    echo "✓ Test configuration exists"
else
    echo "✗ performance-test.yml not found"
    exit 1
fi

# Check processor file
if [ -f "${SCRIPT_DIR}/test-processor.cjs" ]; then
    echo "✓ Test processor exists"
else
    echo "✗ test-processor.cjs not found"
    exit 1
fi

# Test target URL
echo ""
echo "🌐 Testing target URL..."
if curl -s -o /dev/null -w "%{http_code}" "${TARGET_URL}" | grep -q "200"; then
    echo "✓ Target URL is accessible: ${TARGET_URL}"
else
    echo "⚠ Warning: Target URL may not be accessible: ${TARGET_URL}"
fi

# Test stream URL
STREAM_URL="${TARGET_URL}/live-cam/${STREAM_SLUG}"
echo ""
echo "🎥 Testing stream URL..."
if curl -s -o /dev/null -w "%{http_code}" "${STREAM_URL}" | grep -q "200"; then
    echo "✓ Stream URL is accessible: ${STREAM_URL}"
else
    echo "⚠ Warning: Stream URL may not be accessible: ${STREAM_URL}"
fi

# Validate Artillery config
echo ""
echo "📋 Validating Artillery configuration..."
if artillery run --config "${SCRIPT_DIR}/performance-test.yml" --dry-run 2>&1 | grep -q "Dry run mode"; then
    echo "✓ Artillery configuration is valid"
else
    echo "⚠ Warning: Artillery configuration may have issues"
fi

echo ""
echo "✅ Setup validation complete!"
echo ""
echo "To run the full performance test:"
echo "  ./run-performance-test.sh"
echo ""
echo "Research parameters that will be measured:"
echo "  • Latency (HTTP & WebSocket)"
echo "  • Throughput (RPS)"
echo "  • CPU Usage"
echo "  • Memory Usage"
echo "  • Concurrent Connections"
echo "  • Connection Establishment Time"
echo "  • Error Rate"
echo "  • Video Quality Stability (720p & 1080p)"
echo ""
