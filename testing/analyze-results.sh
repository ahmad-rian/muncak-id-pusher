#!/bin/bash

# Results Analysis Script
# Analyzes test results and generates summary statistics

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RESULTS_DIR="${SCRIPT_DIR}/results"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║          Performance Test Results Analysis                ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Find latest results directory
if [ -z "$1" ]; then
    LATEST_RESULT=$(ls -t "${RESULTS_DIR}" 2>/dev/null | head -n 1)
    if [ -z "${LATEST_RESULT}" ]; then
        echo "No test results found in ${RESULTS_DIR}"
        exit 1
    fi
    RESULT_PATH="${RESULTS_DIR}/${LATEST_RESULT}"
else
    RESULT_PATH="$1"
fi

echo -e "${GREEN}Analyzing results from: ${RESULT_PATH}${NC}"
echo ""

# Check if jq is available
HAS_JQ=false
if command -v jq &> /dev/null; then
    HAS_JQ=true
fi

# Analyze Artillery JSON report
if [ -f "${RESULT_PATH}/report.json" ]; then
    echo -e "${YELLOW}═══ Artillery Performance Metrics ═══${NC}"
    echo ""
    
    if [ "$HAS_JQ" = true ]; then
        # Extract metrics using jq
        echo -e "${GREEN}📊 Request Statistics:${NC}"
        echo "  Total Requests: $(jq -r '.aggregate.counters["http.requests"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Successful: $(jq -r '.aggregate.counters["http.codes.200"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Failed: $(jq -r '.aggregate.counters["errors.total"] // 0' "${RESULT_PATH}/report.json")"
        
        echo ""
        echo -e "${GREEN}⚡ Latency Metrics (ms):${NC}"
        echo "  Minimum: $(jq -r '.aggregate.summaries["http.response_time"].min // 0' "${RESULT_PATH}/report.json")"
        echo "  Median (p50): $(jq -r '.aggregate.summaries["http.response_time"].median // 0' "${RESULT_PATH}/report.json")"
        echo "  p95: $(jq -r '.aggregate.summaries["http.response_time"].p95 // 0' "${RESULT_PATH}/report.json")"
        echo "  p99: $(jq -r '.aggregate.summaries["http.response_time"].p99 // 0' "${RESULT_PATH}/report.json")"
        echo "  Maximum: $(jq -r '.aggregate.summaries["http.response_time"].max // 0' "${RESULT_PATH}/report.json")"
        
        echo ""
        echo -e "${GREEN}🔌 Connection Metrics:${NC}"
        echo "  Pusher Connections Success: $(jq -r '.aggregate.counters["pusher_connections_success"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Pusher Connections Failed: $(jq -r '.aggregate.counters["pusher_connections_failed"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Pusher Disconnections: $(jq -r '.aggregate.counters["pusher_disconnections"] // 0' "${RESULT_PATH}/report.json")"
        
        # Connection establishment time
        if jq -e '.aggregate.summaries["connection_establishment_time"]' "${RESULT_PATH}/report.json" > /dev/null 2>&1; then
            echo ""
            echo -e "${GREEN}⏱️  Connection Establishment Time (ms):${NC}"
            echo "  Median: $(jq -r '.aggregate.summaries["connection_establishment_time"].median // 0' "${RESULT_PATH}/report.json")"
            echo "  p95: $(jq -r '.aggregate.summaries["connection_establishment_time"].p95 // 0' "${RESULT_PATH}/report.json")"
            echo "  p99: $(jq -r '.aggregate.summaries["connection_establishment_time"].p99 // 0' "${RESULT_PATH}/report.json")"
        fi
        
        echo ""
        echo -e "${GREEN}💬 Chat Metrics:${NC}"
        echo "  Messages Sent: $(jq -r '.aggregate.counters["chat_messages_sent"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Messages Received: $(jq -r '.aggregate.counters["chat_messages_received"] // 0' "${RESULT_PATH}/report.json")"
        
        echo ""
        echo -e "${GREEN}🎥 Video Quality Metrics:${NC}"
        echo "  720p Stable: $(jq -r '.aggregate.counters["video_quality_720p_stable"] // 0' "${RESULT_PATH}/report.json")"
        echo "  1080p Stable: $(jq -r '.aggregate.counters["video_quality_1080p_stable"] // 0' "${RESULT_PATH}/report.json")"
        echo "  Quality Changes: $(jq -r '.aggregate.counters["video_quality_changes"] // 0' "${RESULT_PATH}/report.json")"
        
        echo ""
        echo -e "${GREEN}👥 Viewer Metrics:${NC}"
        echo "  Viewer Count Updates: $(jq -r '.aggregate.counters["viewer_count_updates"] // 0' "${RESULT_PATH}/report.json")"
        
        # Calculate error rate
        total_requests=$(jq -r '.aggregate.counters["http.requests"] // 0' "${RESULT_PATH}/report.json")
        total_errors=$(jq -r '.aggregate.counters["errors.total"] // 0' "${RESULT_PATH}/report.json")
        
        if [ "$total_requests" -gt 0 ]; then
            error_rate=$(echo "scale=2; ($total_errors / $total_requests) * 100" | bc)
            echo ""
            echo -e "${GREEN}❌ Error Rate:${NC}"
            echo "  ${error_rate}% (${total_errors}/${total_requests})"
        fi
        
    else
        echo "Install 'jq' for detailed metrics analysis: brew install jq"
        echo "Raw JSON report available at: ${RESULT_PATH}/report.json"
    fi
    
    echo ""
fi

# Analyze system metrics
if [ -f "${RESULT_PATH}/system_metrics.csv" ]; then
    echo -e "${YELLOW}═══ System Resource Usage ═══${NC}"
    echo ""
    
    # Calculate statistics
    avg_cpu=$(awk -F',' 'NR>1 {sum+=$2; count++} END {if(count>0) printf "%.2f", sum/count; else print 0}' "${RESULT_PATH}/system_metrics.csv")
    max_cpu=$(awk -F',' 'NR>1 {if($2>max) max=$2} END {printf "%.2f", max}' "${RESULT_PATH}/system_metrics.csv")
    
    avg_memory=$(awk -F',' 'NR>1 {sum+=$3; count++} END {if(count>0) printf "%.2f", sum/count; else print 0}' "${RESULT_PATH}/system_metrics.csv")
    max_memory=$(awk -F',' 'NR>1 {if($3>max) max=$3} END {printf "%.2f", max}' "${RESULT_PATH}/system_metrics.csv")
    
    avg_memory_pct=$(awk -F',' 'NR>1 {sum+=$4; count++} END {if(count>0) printf "%.2f", sum/count; else print 0}' "${RESULT_PATH}/system_metrics.csv")
    max_memory_pct=$(awk -F',' 'NR>1 {if($4>max) max=$4} END {printf "%.2f", max}' "${RESULT_PATH}/system_metrics.csv")
    
    echo -e "${GREEN}💻 CPU Usage:${NC}"
    echo "  Average: ${avg_cpu}%"
    echo "  Peak: ${max_cpu}%"
    
    echo ""
    echo -e "${GREEN}🧠 Memory Usage:${NC}"
    echo "  Average: ${avg_memory} MB (${avg_memory_pct}%)"
    echo "  Peak: ${max_memory} MB (${max_memory_pct}%)"
    
    echo ""
fi

# Analyze server metrics
if [ -f "${RESULT_PATH}/server_metrics.csv" ]; then
    echo -e "${YELLOW}═══ Server Response Metrics ═══${NC}"
    echo ""
    
    avg_response=$(awk -F',' 'NR>1 {sum+=$2; count++} END {if(count>0) printf "%.2f", sum/count; else print 0}' "${RESULT_PATH}/server_metrics.csv")
    min_response=$(awk -F',' 'NR>1 {if(NR==2 || $2<min) min=$2} END {printf "%.2f", min}' "${RESULT_PATH}/server_metrics.csv")
    max_response=$(awk -F',' 'NR>1 {if($2>max) max=$2} END {printf "%.2f", max}' "${RESULT_PATH}/server_metrics.csv")
    
    echo -e "${GREEN}🌐 Server Response Time (ms):${NC}"
    echo "  Average: ${avg_response}"
    echo "  Minimum: ${min_response}"
    echo "  Maximum: ${max_response}"
    
    echo ""
fi

# Summary
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                    Summary                                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}📁 Results Location:${NC}"
echo "  ${RESULT_PATH}"
echo ""
echo -e "${GREEN}📄 Available Reports:${NC}"
[ -f "${RESULT_PATH}/report.json" ] && echo "  ✓ JSON Report: report.json"
[ -f "${RESULT_PATH}/report.html" ] && echo "  ✓ HTML Report: report.html (open in browser)"
[ -f "${RESULT_PATH}/system_metrics.csv" ] && echo "  ✓ System Metrics: system_metrics.csv"
[ -f "${RESULT_PATH}/server_metrics.csv" ] && echo "  ✓ Server Metrics: server_metrics.csv"
[ -f "${RESULT_PATH}/test_output.log" ] && echo "  ✓ Test Log: test_output.log"
echo ""

# Open HTML report if available
if [ -f "${RESULT_PATH}/report.html" ]; then
    echo -e "${YELLOW}💡 Tip: Open the HTML report for visual analysis:${NC}"
    echo "  open ${RESULT_PATH}/report.html"
    echo ""
fi

echo -e "${GREEN}✅ Analysis complete!${NC}"
