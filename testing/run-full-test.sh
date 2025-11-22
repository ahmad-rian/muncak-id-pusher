#!/bin/bash
# run-full-test.sh
# Script untuk menjalankan full test 1 jam dengan monitoring

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TEST_DURATION_HOURS=1
STREAM_URL="quam-modi-dolor-exercitation-voluptates-quasi-culpa-ut-fugiat-aP8DAM"
TARGET="https://pusher.muncak.id"
RESULTS_DIR="./results"
TEST_NAME="pusher-1hour-test"

# Function: Print colored message
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function: Check if stream is live
check_stream_live() {
    print_message $BLUE "Checking if stream is live..."
    
    # Check dengan curl
    response=$(curl -s -o /dev/null -w "%{http_code}" "${TARGET}/live-cam/${STREAM_URL}")
    
    if [ "$response" = "200" ]; then
        print_message $GREEN "✓ Stream is live and accessible"
        return 0
    else
        print_message $RED "✗ Stream is not accessible (HTTP $response)"
        return 1
    fi
}

# Function: Setup monitoring
setup_monitoring() {
    print_message $BLUE "Setting up monitoring..."
    
    # Create results directory
    mkdir -p "$RESULTS_DIR"
    
    # Create monitoring log
    MONITOR_LOG="${RESULTS_DIR}/${TEST_NAME}-monitor-$(date +%s).log"
    
    print_message $GREEN "✓ Monitoring setup complete"
}

# Function: Start system monitoring
start_system_monitoring() {
    print_message $BLUE "Starting system monitoring..."
    
    # Monitor CPU, Memory, Network setiap 10 detik
    {
        while true; do
            echo "=== $(date '+%Y-%m-%d %H:%M:%S') ==="
            
            # CPU Usage
            echo "CPU: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')%"
            
            # Memory Usage
            echo "Memory: $(free -m | awk 'NR==2{printf "%.2f%%", $3*100/$2 }')"
            
            # Network
            if command -v ifstat &> /dev/null; then
                echo "Network: $(ifstat -i eth0 1 1 | tail -n 1)"
            fi
            
            echo ""
            
            sleep 10
        done
    } > "$MONITOR_LOG" 2>&1 &
    
    MONITOR_PID=$!
    print_message $GREEN "✓ System monitoring started (PID: $MONITOR_PID)"
}

# Function: Run Artillery test
run_artillery_test() {
    print_message $BLUE "Starting Artillery load test..."
    print_message $YELLOW "Test duration: ${TEST_DURATION_HOURS} hour(s)"
    print_message $YELLOW "Target: ${TARGET}"
    print_message $YELLOW "Stream: ${STREAM_URL}"
    
    local timestamp=$(date +%s)
    local json_output="${RESULTS_DIR}/${TEST_NAME}-${timestamp}.json"
    local html_output="${RESULTS_DIR}/${TEST_NAME}-${timestamp}.html"
    
    # Run Artillery
    print_message $GREEN "Running test... (this will take ${TEST_DURATION_HOURS} hour)"
    
    artillery run \
        --output "$json_output" \
        --variables "activeStreamSlug=${STREAM_URL}" \
        artillery-pusher-enhanced.yaml
    
    # Generate HTML report
    if [ -f "$json_output" ]; then
        print_message $BLUE "Generating HTML report..."
        artillery report "$json_output" --output "$html_output"
        print_message $GREEN "✓ HTML report generated: $html_output"
    fi
    
    # Generate PDF report
    print_message $BLUE "Generating PDF report..."
    
    # Find latest PDF data file
    local pdf_data=$(ls -t ${RESULTS_DIR}/pusher-pdf-data-*.json 2>/dev/null | head -n 1)
    
    if [ -f "$pdf_data" ]; then
        node generate-pdf-report.js "$pdf_data"
        print_message $GREEN "✓ PDF report generated"
    else
        print_message $YELLOW "⚠ No PDF data found, skipping PDF generation"
    fi
    
    print_message $GREEN "✓ Artillery test completed"
}

# Function: Stop monitoring
stop_monitoring() {
    if [ ! -z "$MONITOR_PID" ]; then
        print_message $BLUE "Stopping system monitoring..."
        kill $MONITOR_PID 2>/dev/null || true
        print_message $GREEN "✓ Monitoring stopped"
    fi
}

# Function: Generate summary
generate_summary() {
    print_message $BLUE "Generating test summary..."
    
    local summary_file="${RESULTS_DIR}/${TEST_NAME}-summary-$(date +%s).txt"
    
    {
        echo "=========================================="
        echo "LOAD TEST SUMMARY"
        echo "=========================================="
        echo ""
        echo "Test Name: $TEST_NAME"
        echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
        echo "Duration: ${TEST_DURATION_HOURS} hour(s)"
        echo "Target: $TARGET"
        echo "Stream: $STREAM_URL"
        echo ""
        echo "=========================================="
        echo "GENERATED FILES"
        echo "=========================================="
        echo ""
        ls -lh "$RESULTS_DIR" | tail -n 10
        echo ""
        echo "=========================================="
        echo "SYSTEM MONITORING LOG"
        echo "=========================================="
        echo ""
        if [ -f "$MONITOR_LOG" ]; then
            echo "Monitor log: $MONITOR_LOG"
            echo "Log size: $(du -h "$MONITOR_LOG" | cut -f1)"
        fi
        echo ""
        echo "=========================================="
        
    } > "$summary_file"
    
    cat "$summary_file"
    
    print_message $GREEN "✓ Summary saved to: $summary_file"
}

# Function: Cleanup
cleanup() {
    print_message $YELLOW "Cleaning up..."
    stop_monitoring
    print_message $GREEN "✓ Cleanup complete"
}

# Main execution
main() {
    print_message $GREEN "=========================================="
    print_message $GREEN "  ARTILLERY LOAD TEST - PUSHER WEBSOCKET"
    print_message $GREEN "=========================================="
    echo ""
    
    # Check prerequisites
    print_message $BLUE "Checking prerequisites..."
    
    if ! command -v artillery &> /dev/null; then
        print_message $RED "✗ Artillery is not installed"
        print_message $YELLOW "Install with: npm install -g artillery"
        exit 1
    fi
    
    if ! command -v node &> /dev/null; then
        print_message $RED "✗ Node.js is not installed"
        exit 1
    fi
    
    print_message $GREEN "✓ Prerequisites check passed"
    echo ""
    
    # Check if stream is live
    if ! check_stream_live; then
        print_message $RED "✗ Cannot proceed: Stream is not live"
        print_message $YELLOW "Please ensure the stream is active at: ${TARGET}/live-cam/${STREAM_URL}"
        exit 1
    fi
    echo ""
    
    # Setup
    setup_monitoring
    
    # Set trap for cleanup on exit
    trap cleanup EXIT INT TERM
    
    # Start monitoring
    start_system_monitoring
    echo ""
    
    # Run test
    run_artillery_test
    echo ""
    
    # Generate summary
    generate_summary
    echo ""
    
    print_message $GREEN "=========================================="
    print_message $GREEN "  TEST COMPLETED SUCCESSFULLY!"
    print_message $GREEN "=========================================="
    echo ""
    print_message $YELLOW "Next steps:"
    print_message $YELLOW "1. Review HTML report in: ${RESULTS_DIR}/"
    print_message $YELLOW "2. Check PDF report for detailed analysis"
    print_message $YELLOW "3. Compare with Laravel Reverb test results"
    echo ""
}

# Run main function
main "$@"