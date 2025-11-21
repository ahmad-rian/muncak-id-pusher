/**
 * MediaRecorder-based Live Streaming Broadcaster
 * Simple, scalable live streaming like YouTube
 * No peer-to-peer, no SDP, no codec issues
 */

const streamId = window.streamId;
const streamSlug = window.streamSlug;
let localStream = null;
let mediaRecorder = null;
let isStreaming = false;
let chunkCounter = 0;
let isMirrored = false;
let streamStartTime = null;
let durationInterval = null;
let thumbnailInterval = null;

// Pusher configuration
const pusher = new Pusher(window.pusherConfig.key, {
    cluster: window.pusherConfig.cluster,
    forceTLS: true
});

const channel = pusher.subscribe(`stream.${streamId}`);
console.log('📡 Broadcaster subscribed to channel:', `stream.${streamId}`);

// Listen for viewer count updates
channel.bind('App\\Events\\ViewerCountUpdated', (data) => {
    console.log('👥 Viewer count updated:', data.count);
    const viewerCountEl = document.getElementById('viewer-count');
    if (viewerCountEl) {
        viewerCountEl.textContent = data.count;
    }
});

// Listen for chat messages
channel.bind('App\\Events\\ChatMessageSent', (data) => {
    console.log('💬 Chat message:', data);
    const chatMonitor = document.getElementById('chat-monitor');
    if (chatMonitor) {
        // Remove "no messages" placeholder
        const placeholder = chatMonitor.querySelector('.text-center');
        if (placeholder) {
            placeholder.remove();
        }

        // Add message
        const messageDiv = document.createElement('div');
        messageDiv.className = 'text-sm';
        messageDiv.innerHTML = `<strong>${data.username}:</strong> ${data.message}`;
        chatMonitor.appendChild(messageDiv);

        // Auto-scroll
        chatMonitor.scrollTop = chatMonitor.scrollHeight;
    }
});

// Setup camera
async function setupCamera() {
    const noCamera = document.getElementById('no-camera');
    const preview = document.getElementById('camera-preview');

    try {
        console.log('📷 Requesting camera...');

        localStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false // No audio - save bandwidth!
        });

        if (preview) {
            preview.srcObject = localStream;
            preview.muted = true;
            await preview.play();

            if (noCamera) noCamera.classList.add('hidden');
            console.log('✅ Camera ready');
        }

        return true;
    } catch (err) {
        console.error('❌ Camera error:', err);
        if (noCamera) noCamera.classList.remove('hidden');
        alert('Cannot access camera: ' + err.message);
        return false;
    }
}

// Update stream duration display
function updateDuration() {
    if (!streamStartTime) return;

    const now = Date.now();
    const elapsed = Math.floor((now - streamStartTime) / 1000);

    const hours = Math.floor(elapsed / 3600);
    const minutes = Math.floor((elapsed % 3600) / 60);
    const seconds = elapsed % 60;

    const formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    const durationEl = document.getElementById('stream-duration');
    if (durationEl) {
        durationEl.textContent = formatted;
    }
}

// Start duration timer
function startDurationTimer() {
    streamStartTime = Date.now();
    updateDuration();
    durationInterval = setInterval(updateDuration, 1000);
}

// Stop duration timer
function stopDurationTimer() {
    if (durationInterval) {
        clearInterval(durationInterval);
        durationInterval = null;
    }
    streamStartTime = null;
    const durationEl = document.getElementById('stream-duration');
    if (durationEl) {
        durationEl.textContent = '00:00:00';
    }
}

// Capture thumbnail once at stream start
async function captureThumbnail() {
    const preview = document.getElementById('camera-preview');

    if (!preview || !localStream) {
        console.warn('⚠️ Cannot capture thumbnail: camera not ready');
        return;
    }

    try {
        // Create canvas
        const canvas = document.createElement('canvas');
        canvas.width = preview.videoWidth || 1280;
        canvas.height = preview.videoHeight || 720;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(preview, 0, 0, canvas.width, canvas.height);

        const imageData = canvas.toDataURL('image/jpeg', 0.85);

        console.log(`📸 Thumbnail captured: ${canvas.width}x${canvas.height}`);

        // Upload thumbnail
        const basePath = window.location.pathname.includes('/admin/live-stream')
            ? `/admin/live-stream/${streamSlug}`
            : `/live-cam/${streamSlug}`;

        const response = await fetch(`${basePath}/thumbnail`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                image: imageData
            })
        });

        const result = await response.json();

        if (result.success) {
            console.log('✅ Thumbnail uploaded:', result.thumbnail_url);
        } else {
            console.error('❌ Thumbnail upload failed:', result.error);
        }

    } catch (error) {
        console.error('❌ Thumbnail capture failed:', error);
    }
}

// Start streaming
async function startStream() {
    console.log('🚀 Starting stream...');

    if (!localStream) {
        alert('Camera not ready. Please refresh the page.');
        return;
    }

    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamSlug}`
        : `/live-cam/${streamSlug}`;

    try {
        // Start stream on server
        const response = await fetch(`${basePath}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                quality: '720p'
            })
        });

        const data = await response.json();

        if (!data.success) {
            alert('Failed to start stream: ' + (data.error || 'Unknown error'));
            return;
        }

        isStreaming = true;
        chunkCounter = 0;

        // Update UI
        const startBtn = document.getElementById('start-button') || document.getElementById('start-stream');
        const stopBtn = document.getElementById('stop-button') || document.getElementById('stop-stream');
        const statusBadge = document.getElementById('stream-status') || document.getElementById('status-badge');

        if (startBtn) startBtn.classList.add('hidden');
        if (stopBtn) stopBtn.classList.remove('hidden');
        if (statusBadge) {
            statusBadge.innerHTML = '<span class="h-2 w-2 rounded-full bg-success"></span> LIVE';
            statusBadge.classList.remove('badge-error');
            statusBadge.classList.add('badge-success');
        }

        // Capture thumbnail once at start
        await captureThumbnail();

        // Start recording with MediaRecorder
        startRecording();

        // Start classification timer
        startClassificationTimer();

        console.log('✅ Stream started');

    } catch (err) {
        console.error('❌ Start stream error:', err);
        alert('Failed to start stream: ' + err.message);
    }
}

// Start MediaRecorder
function startRecording() {
    try {
        // Optimized settings for efficiency (video only)
        const options = {
            mimeType: 'video/webm;codecs=vp8',
            videoBitsPerSecond: 800000  // 800 Kbps - much more efficient for 720p
            // No audio bitrate - audio disabled
        };

        mediaRecorder = new MediaRecorder(localStream, options);

        mediaRecorder.ondataavailable = async (event) => {
            if (event.data && event.data.size > 0 && isStreaming) {
                console.log(`📦 Chunk ${chunkCounter}: ${(event.data.size / 1024).toFixed(2)} KB`);
                await uploadChunk(event.data, chunkCounter++);
            }
        };

        mediaRecorder.onerror = (event) => {
            console.error('❌ MediaRecorder error:', event.error);
        };

        mediaRecorder.onstop = () => {
            console.log('🛑 MediaRecorder stopped');
        };

        // Record in 2-second chunks (optimal balance)
        mediaRecorder.start(2000);
        console.log('🎬 Recording started - 2s chunks at 800 Kbps');

        // Start duration timer
        startDurationTimer();

    } catch (err) {
        console.error('❌ Failed to start MediaRecorder:', err);
        alert('Your browser does not support video recording: ' + err.message);
    }
}

// Upload chunk to server
async function uploadChunk(blob, index) {
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamSlug}`
        : `/live-cam/${streamSlug}`;

    const formData = new FormData();
    formData.append('chunk', blob);
    formData.append('index', index);
    formData.append('timestamp', Date.now());

    try {
        const response = await fetch(`${basePath}/upload-chunk`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            console.log(`✅ Chunk ${index} uploaded`);
        } else {
            console.error(`❌ Failed to upload chunk ${index}:`, data.error);
        }

    } catch (err) {
        console.error(`❌ Upload error for chunk ${index}:`, err);
    }
}

// Stop streaming
async function stopStream() {
    console.log('🛑 Stopping stream...');

    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamSlug}`
        : `/live-cam/${streamSlug}`;

    // Stop MediaRecorder
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    isStreaming = false;

    // Stop classification timer
    stopClassificationTimer();

    // Stop duration timer
    stopDurationTimer();

    try {
        const response = await fetch(`${basePath}/stop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({}) // Send empty JSON body
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Stop stream failed:', response.status, errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 100)}`);
        }

        const data = await response.json();

        if (data.success) {
            console.log('✅ Stream stopped');

            // Update UI
            const startBtn = document.getElementById('start-button') || document.getElementById('start-stream');
            const stopBtn = document.getElementById('stop-button') || document.getElementById('stop-stream');
            const statusBadge = document.getElementById('stream-status') || document.getElementById('status-badge');

            if (startBtn) startBtn.classList.remove('hidden');
            if (stopBtn) stopBtn.classList.add('hidden');
            if (statusBadge) {
                statusBadge.innerHTML = '<span class="h-2 w-2 rounded-full bg-base-content"></span> OFFLINE';
                statusBadge.classList.remove('badge-success');
                statusBadge.classList.add('badge-error');
            }
        } else {
            console.error('❌ Stop stream failed:', data.error || data.message);
        }
    } catch (err) {
        console.error('❌ Stop stream error:', err);
        alert('Failed to stop stream. Please try again or refresh the page.');
    }
}

// Toggle mirror camera
function toggleMirror() {
    isMirrored = !isMirrored;
    const preview = document.getElementById('camera-preview');
    const mirrorBtn = document.getElementById('mirror-camera');

    if (preview) {
        preview.style.transform = isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
        preview.style.transition = 'transform 0.3s ease';
    }

    if (mirrorBtn) {
        // Update button state
        if (isMirrored) {
            mirrorBtn.classList.add('btn-active', 'btn-primary');
        } else {
            mirrorBtn.classList.remove('btn-active', 'btn-primary');
        }
    }

    // Broadcast mirror state to viewers via Pusher
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamSlug}`
        : `/live-cam/${streamSlug}`;

    fetch(`${basePath}/mirror-state`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            is_mirrored: isMirrored
        })
    }).catch(err => console.error('Failed to broadcast mirror state:', err));

    console.log(isMirrored ? '🪞 Camera mirrored' : '📹 Camera normal');
}

// Auto-classification untuk trail conditions
let classificationTimer = null;
const CLASSIFICATION_INTERVAL = 2 * 60 * 1000; // 2 menit (untuk testing)

function captureFrameForClassification() {
    const preview = document.getElementById('camera-preview');

    if (!preview || !localStream || !isStreaming) {
        console.warn('⚠️ Cannot capture: stream not active');
        return;
    }

    try {
        // Create canvas
        const canvas = document.createElement('canvas');
        canvas.width = preview.videoWidth || 1280;
        canvas.height = preview.videoHeight || 720;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(preview, 0, 0, canvas.width, canvas.height);

        const imageData = canvas.toDataURL('image/jpeg', 0.85);

        console.log(`📸 Frame captured for classification: ${canvas.width}x${canvas.height}`);

        // Send to API
        sendFrameForClassification(imageData);

    } catch (error) {
        console.error('❌ Capture failed:', error);
    }
}

async function sendFrameForClassification(imageData) {
    try {
        console.log('🔬 Sending frame for classification...');

        const response = await fetch(`/api/v1/classifications/stream/${streamId}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                image: imageData,
                delay_ms: 2500 // Stream delay
            })
        });

        // Log response status
        console.log(`📡 API Response: ${response.status} ${response.statusText}`);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ API Error Response:', errorText);
            return;
        }

        const result = await response.json();

        if (result.success) {
            console.log('✅ Classification completed:', result.data);
        } else {
            console.error('❌ Classification failed:', result.message);

            // If stream doesn't have hiking trail, stop trying
            if (result.message && result.message.includes('hiking trail')) {
                console.warn('⚠️ Stream tidak memiliki jalur pendakian - classification disabled');
                stopClassificationTimer();
            }
        }
    } catch (error) {
        console.error('❌ Classification error:', error);
    }
}

function startClassificationTimer() {
    if (classificationTimer) {
        clearInterval(classificationTimer);
    }

    console.log(`⏰ Classification timer started (every ${CLASSIFICATION_INTERVAL / 1000 / 60} minutes)`);

    // Initial classification setelah 30 detik
    setTimeout(() => {
        console.log('🚀 Running initial classification...');
        captureFrameForClassification();
    }, 30000);

    // Then repeat setiap 5 menit
    classificationTimer = setInterval(() => {
        console.log('⏰ Auto-classification triggered');
        captureFrameForClassification();
    }, CLASSIFICATION_INTERVAL);
}

function stopClassificationTimer() {
    if (classificationTimer) {
        clearInterval(classificationTimer);
        classificationTimer = null;
        console.log('🛑 Classification timer stopped');
    }
}

// Initialize
console.log('🎥 MSE Broadcaster initializing...');
setupCamera().then(ready => {
    if (ready) {
        console.log('✅ Broadcaster ready');

        // Bind buttons
        const startBtn = document.getElementById('start-button') || document.getElementById('start-stream');
        const stopBtn = document.getElementById('stop-button') || document.getElementById('stop-stream');
        const mirrorBtn = document.getElementById('mirror-camera');

        if (startBtn) startBtn.addEventListener('click', startStream);
        if (stopBtn) stopBtn.addEventListener('click', stopStream);
        if (mirrorBtn) mirrorBtn.addEventListener('click', toggleMirror);
    }
});

// Manual trigger untuk testing
window.manualClassify = () => {
    console.log('🎯 Manual classification triggered');
    captureFrameForClassification();
};
