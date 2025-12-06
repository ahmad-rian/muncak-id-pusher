#!/bin/bash

# Performance Test Runner with System Metrics Collection
# Measures: Latency, Throughput, CPU, Memory, Connections, Errors, Video Quality

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RESULTS_DIR="${SCRIPT_DIR}/results/${TIMESTAMP}"
ARTILLERY_CONFIG="${SCRIPT_DIR}/performance-test.yml"
ENV_FILE="${SCRIPT_DIR}/.env.artillery"

# Create results directory
mkdir -p "${RESULTS_DIR}"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Performance Test - Research Metrics Collection        ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}Test Configuration:${NC}"
echo -e "  Target: https://pusher.muncak.id"
echo -e "  Stream: quam-modi-dolor-exercitation-voluptates-quasi-culpa-ut-fugiat-aP8DAM"
echo -e "  Results: ${RESULTS_DIR}"
echo ""
echo -e "${YELLOW}Research Parameters:${NC}"
echo -e "  ✓ Latency (HTTP & WebSocket)"
echo -e "  ✓ Throughput (RPS)"
echo -e "  ✓ CPU Usage"
echo -e "  ✓ Memory Usage"
echo -e "  ✓ Concurrent Connections"
echo -e "  ✓ Connection Establishment Time"
echo -e "  ✓ Error Rate"
echo -e "  ✓ Video Quality Stability (720p & 1080p)"
echo ""

# Load environment variables
if [ -f "${ENV_FILE}" ]; then
    export $(cat "${ENV_FILE}" | grep -v '^#' | xargs)
    echo -e "${GREEN}✓ Environment variables loaded${NC}"
else
    echo -e "${RED}✗ Error: .env.artillery file not found${NC}"
    exit 1
fi

# Check if Artillery is installed
if ! command -v artillery &> /dev/null; then
    echo -e "${RED}✗ Artillery is not installed${NC}"
    echo -e "${YELLOW}Installing Artillery...${NC}"
    npm install -g artillery
fi

# Check if pusher-js is installed locally
if [ ! -d "${SCRIPT_DIR}/node_modules/pusher-js" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    cd "${SCRIPT_DIR}"
    npm install pusher-js
fi

echo -e "${GREEN}✓ All dependencies ready${NC}"
echo ""

# Function to collect system metrics
collect_system_metrics() {
    local output_file=$1
    local interval=5  # Collect every 5 seconds
    
    echo "timestamp,cpu_percent,memory_mb,memory_percent" > "${output_file}"
    
    while true; do
        # Get CPU usage (average across all cores)
        cpu_usage=$(ps -A -o %cpu | awk '{s+=$1} END {print s}')
        
        # Get memory usage
        memory_info=$(vm_stat | perl -ne '/page size of (\d+)/ and $size=$1; /Pages active:\s+(\d+)/ and printf("%.0f\n", $1 * $size / 1048576);')
        memory_total=$(sysctl -n hw.memsize | awk '{print $1/1048576}')
        memory_percent=$(echo "scale=2; ${memory_info} / ${memory_total} * 100" | bc)
        
        # Record timestamp and metrics
        timestamp=$(date +%Y-%m-%d\ %H:%M:%S)
        echo "${timestamp},${cpu_usage},${memory_info},${memory_percent}" >> "${output_file}"
        
        sleep ${interval}
    done
}

# Function to monitor server metrics (if accessible)
monitor_server_metrics() {
    local output_file=$1
    local target_url="https://pusher.muncak.id"
    
    echo "timestamp,response_time_ms,status_code" > "${output_file}"
    
    while true; do
        timestamp=$(date +%Y-%m-%d\ %H:%M:%S)
        
        # Ping the server and measure response time
        response_time=$(curl -o /dev/null -s -w '%{time_total}\n' "${target_url}" | awk '{print $1*1000}')
        status_code=$(curl -o /dev/null -s -w '%{http_code}\n' "${target_url}")
        
        echo "${timestamp},${response_time},${status_code}" >> "${output_file}"
        
        sleep 10
    done
}

echo -e "${BLUE}Starting system metrics collection...${NC}"

# Start system metrics collection in background
collect_system_metrics "${RESULTS_DIR}/system_metrics.csv" &
SYSTEM_METRICS_PID=$!

# Start server monitoring in background
monitor_server_metrics "${RESULTS_DIR}/server_metrics.csv" &
SERVER_METRICS_PID=$!

echo -e "${GREEN}✓ System metrics collection started (PID: ${SYSTEM_METRICS_PID})${NC}"
echo -e "${GREEN}✓ Server monitoring started (PID: ${SERVER_METRICS_PID})${NC}"
echo ""

# Cleanup function
cleanup() {
    echo ""
    echo -e "${YELLOW}Stopping metrics collection...${NC}"
    
    # Kill background processes
    kill ${SYSTEM_METRICS_PID} 2>/dev/null || true
    kill ${SERVER_METRICS_PID} 2>/dev/null || true
    
    echo -e "${GREEN}✓ Metrics collection stopped${NC}"
    
    # Generate summary report
    generate_summary_report
}

# Generate summary report
generate_summary_report() {
    echo ""
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║              Test Results Summary                          ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    
    # Check if JSON report exists
    if [ -f "${RESULTS_DIR}/report.json" ]; then
        echo -e "${GREEN}✓ Artillery JSON report: ${RESULTS_DIR}/report.json${NC}"
        
        # Extract key metrics from JSON report
        echo ""
        echo -e "${YELLOW}Key Metrics:${NC}"
        
        # Use jq if available, otherwise use basic grep
        if command -v jq &> /dev/null; then
            echo -e "  Total Requests: $(jq '.aggregate.counters["http.requests"] // 0' "${RESULTS_DIR}/report.json")"
            echo -e "  Total Errors: $(jq '.aggregate.counters["errors.total"] // 0' "${RESULTS_DIR}/report.json")"
            echo -e "  Median Latency: $(jq '.aggregate.summaries["http.response_time"].median // 0' "${RESULTS_DIR}/report.json")ms"
            echo -e "  P95 Latency: $(jq '.aggregate.summaries["http.response_time"].p95 // 0' "${RESULTS_DIR}/report.json")ms"
            echo -e "  P99 Latency: $(jq '.aggregate.summaries["http.response_time"].p99 // 0' "${RESULTS_DIR}/report.json")ms"
        fi
    fi
    
    if [ -f "${RESULTS_DIR}/report.html" ]; then
        echo -e "${GREEN}✓ Artillery HTML report: ${RESULTS_DIR}/report.html${NC}"
    fi
    
    if [ -f "${RESULTS_DIR}/system_metrics.csv" ]; then
        echo -e "${GREEN}✓ System metrics: ${RESULTS_DIR}/system_metrics.csv${NC}"
        
        # Calculate average CPU and Memory
        avg_cpu=$(awk -F',' 'NR>1 {sum+=$2; count++} END {if(count>0) print sum/count; else print 0}' "${RESULTS_DIR}/system_metrics.csv")
        avg_memory=$(awk -F',' 'NR>1 {sum+=$3; count++} END {if(count>0) print sum/count; else print 0}' "${RESULTS_DIR}/system_metrics.csv")
        
        echo ""
        echo -e "${YELLOW}System Resource Usage:${NC}"
        echo -e "  Average CPU: ${avg_cpu}%"
        echo -e "  Average Memory: ${avg_memory} MB"
    fi
    
    if [ -f "${RESULTS_DIR}/server_metrics.csv" ]; then
        echo -e "${GREEN}✓ Server metrics: ${RESULTS_DIR}/server_metrics.csv${NC}"
    fi
    
    echo ""
    echo -e "${BLUE}All results saved to: ${RESULTS_DIR}${NC}"
    echo ""
}

# Set trap to cleanup on exit
trap cleanup EXIT INT TERM

# Run Artillery test
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║              Starting Artillery Load Test                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

cd "${SCRIPT_DIR}"

# Run Artillery with JSON output
# The YML file is the test script itself in Artillery 2.0
artillery run "${ARTILLERY_CONFIG}" \
    --output "${RESULTS_DIR}/report.json" \
    2>&1 | tee "${RESULTS_DIR}/test_output.log"

# Generate HTML report from JSON
if [ -f "${RESULTS_DIR}/report.json" ]; then
    echo ""
    echo -e "${YELLOW}Generating Artillery HTML report...${NC}"
    artillery report "${RESULTS_DIR}/report.json" --output "${RESULTS_DIR}/report.html"
    echo -e "${GREEN}✓ Artillery HTML report generated${NC}"
fi

# Generate comprehensive HTML and PDF reports
echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║          Generating Comprehensive Reports                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

if [ -f "${SCRIPT_DIR}/generate-reports.sh" ]; then
    "${SCRIPT_DIR}/generate-reports.sh" "${RESULTS_DIR}"
else
    echo -e "${YELLOW}⚠ Report generator not found, skipping...${NC}"
fi

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║              Test Completed Successfully!                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
