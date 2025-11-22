#!/bin/bash
# run-5x-tests.sh
# Script untuk menjalankan test 5 kali sesuai metodologi penelitian

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
TARGET_SERVICE=$1  # "pusher" atau "reverb"
COOLING_PERIOD=300  # 5 menit dalam detik
ITERATIONS=5

if [ -z "$TARGET_SERVICE" ]; then
    echo -e "${RED}Error: Target service not specified${NC}"
    echo "Usage: bash run-5x-tests.sh [pusher|reverb]"
    exit 1
fi

if [ "$TARGET_SERVICE" != "pusher" ] && [ "$TARGET_SERVICE" != "reverb" ]; then
    echo -e "${RED}Error: Invalid target service${NC}"
    echo "Use: pusher or reverb"
    exit 1
fi

echo -e "${GREEN}=========================================="
echo "  5X LOAD TEST - ${TARGET_SERVICE^^}"
echo "==========================================${NC}"
echo ""

# Create main results directory
MAIN_RESULTS_DIR="./results/5x-${TARGET_SERVICE}"
mkdir -p "$MAIN_RESULTS_DIR"

# Array untuk menyimpan hasil
declare -a test_results

# Function: Run single test iteration
run_iteration() {
    local iteration=$1
    
    echo -e "${BLUE}=========================================="
    echo "  ITERATION $iteration of $ITERATIONS"
    echo "==========================================${NC}"
    echo ""
    
    # Create iteration directory
    ITERATION_DIR="${MAIN_RESULTS_DIR}/iteration-${iteration}"
    mkdir -p "$ITERATION_DIR"
    
    # Set timestamp
    TIMESTAMP=$(date +%s)
    
    # Run test berdasarkan service
    if [ "$TARGET_SERVICE" = "pusher" ]; then
        echo -e "${YELLOW}Running Pusher test...${NC}"
        
        artillery run \
            --output "${ITERATION_DIR}/pusher-test-${TIMESTAMP}.json" \
            --variables "activeStreamSlug=quam-modi-dolor-exercitation-voluptates-quasi-culpa-ut-fugiat-aP8DAM" \
            artillery-pusher-enhanced.yaml
        
        # Generate reports
        local json_file="${ITERATION_DIR}/pusher-test-${TIMESTAMP}.json"
        
        if [ -f "$json_file" ]; then
            echo -e "${BLUE}Generating HTML report...${NC}"
            artillery report "$json_file" --output "${ITERATION_DIR}/report-${iteration}.html"
            
            # Extract metrics
            extract_metrics "$json_file" "$iteration"
        fi
        
    elif [ "$TARGET_SERVICE" = "reverb" ]; then
        echo -e "${YELLOW}Running Reverb test...${NC}"
        
        artillery run \
            --output "${ITERATION_DIR}/reverb-test-${TIMESTAMP}.json" \
            --variables "activeStreamSlug=quam-modi-dolor-exercitation-voluptates-quasi-culpa-ut-fugiat-aP8DAM" \
            artillery-reverb-enhanced.yaml
        
        # Generate reports
        local json_file="${ITERATION_DIR}/reverb-test-${TIMESTAMP}.json"
        
        if [ -f "$json_file" ]; then
            echo -e "${BLUE}Generating HTML report...${NC}"
            artillery report "$json_file" --output "${ITERATION_DIR}/report-${iteration}.html"
            
            # Extract metrics
            extract_metrics "$json_file" "$iteration"
        fi
    fi
    
    echo -e "${GREEN}✓ Iteration $iteration completed${NC}"
    echo ""
}

# Function: Extract key metrics
extract_metrics() {
    local json_file=$1
    local iteration=$2
    
    echo -e "${BLUE}Extracting metrics from iteration $iteration...${NC}"
    
    # Parse JSON menggunakan node
    node -e "
        const fs = require('fs');
        const data = JSON.parse(fs.readFileSync('$json_file', 'utf8'));
        
        const metrics = {
            iteration: $iteration,
            scenarios_completed: data.aggregate.counters['vusers.completed'] || 0,
            scenarios_created: data.aggregate.counters['vusers.created'] || 0,
            requests_completed: data.aggregate.counters['http.requests'] || 0,
            median_latency: data.aggregate.latency?.median || 0,
            p95_latency: data.aggregate.latency?.p95 || 0,
            p99_latency: data.aggregate.latency?.p99 || 0,
            min_latency: data.aggregate.latency?.min || 0,
            max_latency: data.aggregate.latency?.max || 0,
            errors: data.aggregate.counters['errors.total'] || 0
        };
        
        console.log(JSON.stringify(metrics, null, 2));
        
        // Save ke file
        fs.writeFileSync('${ITERATION_DIR}/metrics-${iteration}.json', JSON.stringify(metrics, null, 2));
    " > /dev/null
    
    echo -e "${GREEN}✓ Metrics extracted${NC}"
}

# Function: Cooling period
cooling_period() {
    local seconds=$COOLING_PERIOD
    
    echo -e "${YELLOW}Cooling period: ${seconds}s${NC}"
    echo "Allowing server to stabilize..."
    
    while [ $seconds -gt 0 ]; do
        echo -ne "${BLUE}Time remaining: ${seconds}s${NC}\r"
        sleep 1
        : $((seconds--))
    done
    
    echo -e "\n${GREEN}✓ Cooling period complete${NC}\n"
}

# Main execution
echo "Starting 5x load test for: ${TARGET_SERVICE}"
echo "Each test will run for approximately 1 hour"
echo "Total estimated time: ~5.5 hours (including cooling periods)"
echo ""

read -p "Press Enter to start, or Ctrl+C to cancel..."
echo ""

# Run 5 iterations
for i in $(seq 1 $ITERATIONS); do
    run_iteration $i
    
    # Cooling period (except after last iteration)
    if [ $i -lt $ITERATIONS ]; then
        cooling_period
    fi
done

# Generate combined analysis
echo -e "${BLUE}=========================================="
echo "  GENERATING COMBINED ANALYSIS"
echo "==========================================${NC}"
echo ""

# Combine all metrics
echo -e "${BLUE}Combining metrics from all iterations...${NC}"

node -e "
    const fs = require('fs');
    const path = require('path');
    
    const allMetrics = [];
    
    // Read all iteration metrics
    for (let i = 1; i <= $ITERATIONS; i++) {
        const metricsFile = path.join('$MAIN_RESULTS_DIR', \`iteration-\${i}\`, \`metrics-\${i}.json\`);
        if (fs.existsSync(metricsFile)) {
            const data = JSON.parse(fs.readFileSync(metricsFile, 'utf8'));
            allMetrics.push(data);
        }
    }
    
    // Calculate statistics
    const calculateStats = (values) => {
        const sorted = values.sort((a, b) => a - b);
        const sum = values.reduce((a, b) => a + b, 0);
        const mean = sum / values.length;
        
        // Calculate standard deviation
        const variance = values.reduce((sq, n) => sq + Math.pow(n - mean, 2), 0) / values.length;
        const stdDev = Math.sqrt(variance);
        
        return {
            min: sorted[0],
            max: sorted[sorted.length - 1],
            median: sorted[Math.floor(sorted.length / 2)],
            mean: mean,
            stdDev: stdDev
        };
    };
    
    // Extract latency values
    const medianLatencies = allMetrics.map(m => m.median_latency);
    const p95Latencies = allMetrics.map(m => m.p95_latency);
    const p99Latencies = allMetrics.map(m => m.p99_latency);
    
    const combinedAnalysis = {
        service: '$TARGET_SERVICE',
        iterations: $ITERATIONS,
        timestamp: new Date().toISOString(),
        individual_results: allMetrics,
        statistics: {
            median_latency: calculateStats(medianLatencies),
            p95_latency: calculateStats(p95Latencies),
            p99_latency: calculateStats(p99Latencies)
        }
    };
    
    // Save combined analysis
    fs.writeFileSync(
        '$MAIN_RESULTS_DIR/combined-analysis.json',
        JSON.stringify(combinedAnalysis, null, 2)
    );
    
    console.log(JSON.stringify(combinedAnalysis, null, 2));
"

echo -e "${GREEN}✓ Combined analysis saved${NC}"

# Generate summary report
SUMMARY_FILE="${MAIN_RESULTS_DIR}/summary-report.txt"

{
    echo "=========================================="
    echo "  5X LOAD TEST SUMMARY"
    echo "  Service: ${TARGET_SERVICE^^}"
    echo "=========================================="
    echo ""
    echo "Test Date: $(date '+%Y-%m-%d %H:%M:%S')"
    echo "Iterations: $ITERATIONS"
    echo ""
    echo "=========================================="
    echo "  RESULTS BY ITERATION"
    echo "=========================================="
    echo ""
    
    for i in $(seq 1 $ITERATIONS); do
        echo "--- Iteration $i ---"
        if [ -f "${MAIN_RESULTS_DIR}/iteration-${i}/metrics-${i}.json" ]; then
            cat "${MAIN_RESULTS_DIR}/iteration-${i}/metrics-${i}.json"
        fi
        echo ""
    done
    
    echo "=========================================="
    echo "  COMBINED STATISTICS"
    echo "=========================================="
    echo ""
    
    if [ -f "${MAIN_RESULTS_DIR}/combined-analysis.json" ]; then
        cat "${MAIN_RESULTS_DIR}/combined-analysis.json"
    fi
    
} > "$SUMMARY_FILE"

echo -e "${GREEN}=========================================="
echo "  ALL TESTS COMPLETED!"
echo "==========================================${NC}"
echo ""
echo "Results saved to: $MAIN_RESULTS_DIR"
echo "Summary report: $SUMMARY_FILE"
echo ""
echo "Next steps:"
echo "1. Review individual iteration reports"
echo "2. Check combined-analysis.json for statistics"
echo "3. Generate comparison report if both services tested"
echo ""

# Generate PDF jika PDF data tersedia
echo -e "${BLUE}Checking for PDF data...${NC}"

PDF_DATA_FILES=$(find "$MAIN_RESULTS_DIR" -name "*pdf-data*.json" 2>/dev/null)

if [ ! -z "$PDF_DATA_FILES" ]; then
    echo -e "${GREEN}Found PDF data files. Generating PDFs...${NC}"
    
    for pdf_data in $PDF_DATA_FILES; do
        echo "Generating PDF for: $pdf_data"
        node generate-pdf-report.js "$pdf_data"
    done
    
    echo -e "${GREEN}✓ PDF reports generated${NC}"
else
    echo -e "${YELLOW}No PDF data found${NC}"
fi

echo ""
echo -e "${GREEN}✓ Testing complete!${NC}"