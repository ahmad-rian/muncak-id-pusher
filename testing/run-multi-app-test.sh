#!/bin/bash
# run-multi-app-test.sh
# Script untuk run test 5x dengan auto-switch Pusher apps
# Kalau 1 app over quota, otomatis switch ke app berikutnya

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration - 5 Pusher Apps for Auto-Switch Testing
declare -a PUSHER_APPS=(
    "3b7e0d850b4ba0c792a5:2081349"  # App A (baru)
    "f1613f42170f59122bbb:2081350"  # App B (baru)
    "72a59c27088480530c54:2081337"  # App C (lama, masih aktif)
    "b688e9a798c5c1c700d9:2081338"  # App D (lama, masih aktif)
    "7ab2ae6ef27e5d8acd3e:2081339"  # App E (lama, masih aktif)
)

ITERATIONS=5
RESULTS_DIR="./results/multi-app-test"
ARTILLERY_CONFIG="./artillery.yaml"
COOLING_PERIOD=20

echo -e "${GREEN}=========================================="
echo "  MULTI-APP LOAD TEST - AUTO SWITCH"
echo "==========================================${NC}"
echo ""
echo "Strategy: Run $ITERATIONS tests with auto app switching"
echo "Apps configured: ${#PUSHER_APPS[@]}"
echo ""

# Create results directory
mkdir -p "$RESULTS_DIR"

# Function: Update Pusher key in artillery.yaml
update_pusher_key() {
    local pusher_key=$1
    local app_id=$2
    
    echo -e "${BLUE}Updating artillery.yaml with App ID: $app_id${NC}"
    
    # Backup original
    cp "$ARTILLERY_CONFIG" "${ARTILLERY_CONFIG}.backup"
    
    # Update pusherKey & cluster
    sed -i '' "s/pusherKey: \".*\"/pusherKey: \"$pusher_key\"/" "$ARTILLERY_CONFIG"
    sed -i '' "s/pusherCluster: \".*\"/pusherCluster: \"ap1\"/" "$ARTILLERY_CONFIG"
    
    echo -e "${GREEN}✓ Updated to use Pusher App: $app_id${NC}"
}

# Function: Restore original config
restore_config() {
    if [ -f "${ARTILLERY_CONFIG}.backup" ]; then
        mv "${ARTILLERY_CONFIG}.backup" "$ARTILLERY_CONFIG"
        echo -e "${GREEN}✓ Config restored${NC}"
    fi
}

# Function: Run single test iteration
run_test_iteration() {
    local iteration=$1
    local pusher_app=$2
    
    # Parse app credentials
    IFS=':' read -r pusher_key app_id <<< "$pusher_app"
    
    echo -e "${BLUE}=========================================="
    echo "  ITERATION $iteration of $ITERATIONS"
    echo "  Using Pusher App: $app_id"
    echo "==========================================${NC}"
    echo ""
    
    # Update config
    update_pusher_key "$pusher_key" "$app_id"
    
    # Create iteration directory
    ITERATION_DIR="${RESULTS_DIR}/iteration-${iteration}"
    mkdir -p "$ITERATION_DIR"
    
    # Set timestamp
    TIMESTAMP=$(date +%s)
    
    # Run Artillery test
    echo -e "${YELLOW}Running test...${NC}"
    
    local json_output="${ITERATION_DIR}/pusher-test-${TIMESTAMP}.json"
    local test_success=false
    
    # Run test and capture output
    if artillery run \
        --output "$json_output" \
        --variables '{"activeStreamSlug":"quam-modi-dolor-exercitation-voluptates-quasi-culpa-ut-fugiat-aP8DAM"}' \
        "$ARTILLERY_CONFIG" 2>&1 | tee "${ITERATION_DIR}/test-output.log"; then
        
        # Quota/error detection
        if grep -Ei "(over quota|quota|rate limit|429|limit exceeded)" "${ITERATION_DIR}/test-output.log" >/dev/null; then
            echo -e "${RED}✗ Detected quota/limit errors for App $app_id${NC}"
            test_success=false
        else
            # Inspect JSON counters for errors
            if [ -f "$json_output" ]; then
                errors_total=$(node -e "const d=require('fs').existsSync('$json_output')?JSON.parse(require('fs').readFileSync('$json_output','utf8')):null; console.log(d? (d.aggregate?.counters?.['errors.total']||0) : 0)")
                if [ "$errors_total" != "0" ]; then
                    echo -e "${YELLOW}⚠ Errors detected in report (errors.total=$errors_total)${NC}"
                    test_success=false
                else
                    echo -e "${GREEN}✓ Test completed successfully${NC}"
                    test_success=true
                fi
            else
                # No JSON output means something went wrong
                echo -e "${YELLOW}⚠ No JSON output produced${NC}"
                test_success=false
            fi
        fi
    else
        echo -e "${RED}✗ Test failed${NC}"
        test_success=false
    fi
    
    # Generate reports if test succeeded
    if [ "$test_success" = true ] && [ -f "$json_output" ]; then
        echo -e "${BLUE}Generating reports...${NC}"
        
        # HTML report
        artillery report "$json_output" --output "${ITERATION_DIR}/report-${iteration}.html" 2>/dev/null || true
        
        # Extract metrics
        node -e "
            const fs = require('fs');
            try {
                const data = JSON.parse(fs.readFileSync('$json_output', 'utf8'));
                const metrics = {
                    iteration: $iteration,
                    app_id: '$app_id',
                    timestamp: '$TIMESTAMP',
                    scenarios_completed: data.aggregate.counters['vusers.completed'] || 0,
                    scenarios_created: data.aggregate.counters['vusers.created'] || 0,
                    median_latency: data.aggregate.latency?.median || 0,
                    p95_latency: data.aggregate.latency?.p95 || 0,
                    p99_latency: data.aggregate.latency?.p99 || 0,
                    errors: data.aggregate.counters['errors.total'] || 0
                };
                fs.writeFileSync('${ITERATION_DIR}/metrics-${iteration}.json', JSON.stringify(metrics, null, 2));
                console.log('✓ Metrics extracted');
            } catch (err) {
                console.error('Error extracting metrics:', err.message);
            }
        " 2>/dev/null || true
        
        echo -e "${GREEN}✓ Iteration $iteration completed${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠ Iteration $iteration failed or quota exceeded${NC}"
        return 1
    fi
}

# Function: Cooling period
cooling_period() {
    local seconds=$COOLING_PERIOD
    echo -e "${YELLOW}Cooling period: ${seconds}s${NC}"
    
    while [ $seconds -gt 0 ]; do
        echo -ne "${BLUE}Time remaining: ${seconds}s${NC}\r"
        sleep 1
        : $((seconds--))
    done
    
    echo -e "\n${GREEN}✓ Ready for next iteration${NC}\n"
}

# Main execution
echo "Starting multi-app load test..."
echo ""

read -p "Press Enter to start, or Ctrl+C to cancel..."
echo ""

successful_iterations=0
current_app_index=0

# Run iterations
for i in $(seq 1 $ITERATIONS); do
    # Check if we have apps left
    if [ $current_app_index -ge ${#PUSHER_APPS[@]} ]; then
        echo -e "${RED}✗ All apps exhausted! Completed $successful_iterations/$ITERATIONS iterations${NC}"
        break
    fi
    
    # Get current app
    current_app="${PUSHER_APPS[$current_app_index]}"
    
    # Run test
    if run_test_iteration $i "$current_app"; then
        ((successful_iterations++))
        
        # Cooling period between iterations (except last)
        if [ $i -lt $ITERATIONS ]; then
            cooling_period
        fi
    else
        # Test failed (likely quota exceeded), try next app
        echo -e "${YELLOW}Switching to next Pusher app...${NC}"
        ((current_app_index++))
        
        # Retry same iteration with new app
        if [ $current_app_index -lt ${#PUSHER_APPS[@]} ]; then
            echo -e "${BLUE}Retrying iteration $i with app $((current_app_index + 1))${NC}"
            ((i--))  # Decrement to retry same iteration
        fi
    fi
done

# Restore original config
restore_config

# Generate combined analysis
echo -e "${BLUE}=========================================="
echo "  GENERATING COMBINED ANALYSIS"
echo "==========================================${NC}"
echo ""

node -e "
    const fs = require('fs');
    const path = require('path');
    
    const allMetrics = [];
    
    // Read all successful iteration metrics
    for (let i = 1; i <= $ITERATIONS; i++) {
        const metricsFile = path.join('$RESULTS_DIR', \`iteration-\${i}\`, \`metrics-\${i}.json\`);
        if (fs.existsSync(metricsFile)) {
            const data = JSON.parse(fs.readFileSync(metricsFile, 'utf8'));
            allMetrics.push(data);
        }
    }
    
    if (allMetrics.length === 0) {
        console.log('No successful iterations found');
        process.exit(0);
    }
    
    // Calculate statistics
    const calculateStats = (values) => {
        const sorted = values.sort((a, b) => a - b);
        const sum = values.reduce((a, b) => a + b, 0);
        const mean = sum / values.length;
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
    
    const medianLatencies = allMetrics.map(m => m.median_latency);
    const p95Latencies = allMetrics.map(m => m.p95_latency);
    const p99Latencies = allMetrics.map(m => m.p99_latency);
    
    const combinedAnalysis = {
        total_iterations: allMetrics.length,
        successful_iterations: allMetrics.length,
        timestamp: new Date().toISOString(),
        individual_results: allMetrics,
        statistics: {
            median_latency: calculateStats(medianLatencies),
            p95_latency: calculateStats(p95Latencies),
            p99_latency: calculateStats(p99Latencies)
        }
    };
    
    fs.writeFileSync(
        '$RESULTS_DIR/combined-analysis.json',
        JSON.stringify(combinedAnalysis, null, 2)
    );
    
    console.log('✓ Combined analysis saved');
    console.log(JSON.stringify(combinedAnalysis.statistics, null, 2));
" 2>/dev/null || echo "Error generating combined analysis"

echo ""
echo -e "${GREEN}=========================================="
echo "  TEST COMPLETED!"
echo "==========================================${NC}"
echo ""
echo "Successful iterations: $successful_iterations/$ITERATIONS"
echo "Results saved to: $RESULTS_DIR"
echo ""
echo "Next steps:"
echo "1. Review results in: $RESULTS_DIR"
echo "2. Check combined-analysis.json for statistics"
echo "3. Generate PDF reports if needed"
echo ""
echo -e "${GREEN}✓ Multi-app testing complete!${NC}"
