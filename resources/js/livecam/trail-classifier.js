/**
 * Trail Classification Display System
 * Display classification results di viewer page
 * (Capturing dilakukan dari broadcaster, bukan viewer)
 */

const streamId = window.streamId;
let refreshTimer = null;
const REFRESH_INTERVAL = 120 * 1000; // Refresh setiap 2 menit untuk update UI
let classificationEnabled = false; // Disable auto-refresh by default

console.log('🔬 Trail Classification Display initialized');

// Removed capture logic - now handled by broadcaster

/**
 * Update classification UI dengan hasil
 */
function updateClassificationDisplay(data) {
    const container = document.getElementById('classification-display');

    if (!container) return;

    // Update time
    const timeEl = document.getElementById('classification-time');
    if (timeEl) {
        timeEl.textContent = data.classified_at || 'Baru saja';
    }

    // Update weather
    const weatherEl = document.getElementById('classification-weather');
    if (weatherEl) {
        weatherEl.textContent = data.weather_label || '-';
    }

    // Update crowd
    const crowdEl = document.getElementById('classification-crowd');
    if (crowdEl) {
        crowdEl.textContent = data.crowd_label || '-';
    }

    // Update visibility
    const visibilityEl = document.getElementById('classification-visibility');
    if (visibilityEl) {
        visibilityEl.textContent = data.visibility_label || '-';
    }

    // Update recommendation
    const recommendationEl = document.getElementById('classification-recommendation');
    if (recommendationEl) {
        recommendationEl.textContent = data.recommendation || '';
    }

    // Remove processing state
    container.classList.remove('opacity-50');

    // Show container if hidden
    container.classList.remove('hidden');

    console.log('🎨 Classification UI updated');
}

/**
 * Update UI state (processing/error)
 */
function updateClassificationUI(state, message = '') {
    const container = document.getElementById('classification-display');

    if (!container) return;

    if (state === 'processing') {
        container.classList.add('opacity-50');
        const timeEl = document.getElementById('classification-time');
        if (timeEl) {
            timeEl.textContent = 'Menganalisis...';
        }
    } else if (state === 'error') {
        container.classList.remove('opacity-50');
        const timeEl = document.getElementById('classification-time');
        if (timeEl) {
            timeEl.textContent = 'Gagal menganalisis';
        }
    }
}

/**
 * Load latest classification saat page load
 */
async function loadLatestClassification() {
    // Skip if classification not enabled
    if (!classificationEnabled) {
        return;
    }

    try {
        const response = await fetch(`/api/v1/classifications/stream/${streamId}/latest`);

        // Only process if response is OK (200)
        if (!response.ok) {
            return; // Silent fail - no logging
        }

        const result = await response.json();

        if (result.success) {
            console.log('📊 Latest classification loaded:', result.data);
            updateClassificationDisplay(result.data);
            classificationEnabled = true; // Enable auto-refresh after first successful load

            // Update time dengan human readable
            const timeEl = document.getElementById('classification-time');
            if (timeEl && result.data.classified_at_human) {
                timeEl.textContent = result.data.classified_at_human;
            }
        }
    } catch (error) {
        // Silent fail
    }
}

/**
 * Start auto-refresh timer untuk poll latest data
 */
function startRefreshTimer() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }

    // Don't start timer if classification not enabled yet
    if (!classificationEnabled) {
        return;
    }

    console.log(`⏰ Auto-refresh started (every ${REFRESH_INTERVAL / 1000} seconds)`);

    // Refresh setiap 30 detik
    refreshTimer = setInterval(() => {
        loadLatestClassification();
    }, REFRESH_INTERVAL);
}

/**
 * Stop auto-refresh timer
 */
function stopRefreshTimer() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
        console.log('🛑 Auto-refresh stopped');
    }
}

// Initialize saat stream started
const channel = window.pusher?.subscribe(`stream.${streamId}`);

if (channel) {
    // Listen for classification-ready event from broadcaster
    channel.bind('classification-ready', () => {
        console.log('📊 Classification data available - enabling display');
        classificationEnabled = true;
        loadLatestClassification();
        startRefreshTimer();
    });

    channel.bind('stream-ended', () => {
        stopRefreshTimer();
        classificationEnabled = false;
    });
}

console.log('✅ Trail Classification Display ready');
console.log('💡 Waiting for classification data from broadcaster...');
