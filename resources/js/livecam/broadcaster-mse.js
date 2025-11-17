/**
 * MediaRecorder-based Live Streaming Broadcaster
 * Simple, scalable live streaming like YouTube
 * No peer-to-peer, no SDP, no codec issues
 */

const streamId = window.streamId;
let localStream = null;
let mediaRecorder = null;
let isStreaming = false;
let chunkCounter = 0;

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
            audio: true
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

// Start streaming
async function startStream() {
    console.log('🚀 Starting stream...');

    if (!localStream) {
        alert('Camera not ready. Please refresh the page.');
        return;
    }

    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

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

        // Start recording with MediaRecorder
        startRecording();

        console.log('✅ Stream started');

    } catch (err) {
        console.error('❌ Start stream error:', err);
        alert('Failed to start stream: ' + err.message);
    }
}

// Start MediaRecorder
function startRecording() {
    try {
        // Optimized settings for efficiency
        const options = {
            mimeType: 'video/webm;codecs=vp8,opus',
            videoBitsPerSecond: 800000,  // 800 Kbps - much more efficient for 720p
            audioBitsPerSecond: 64000    // 64 Kbps for audio
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

        // Record in 1-second chunks (lower latency)
        mediaRecorder.start(1000);
        console.log('🎬 Recording started - 1s chunks at 800 Kbps');

    } catch (err) {
        console.error('❌ Failed to start MediaRecorder:', err);
        alert('Your browser does not support video recording: ' + err.message);
    }
}

// Upload chunk to server
async function uploadChunk(blob, index) {
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

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
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

    // Stop MediaRecorder
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    isStreaming = false;

    try {
        const response = await fetch(`${basePath}/stop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

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
        }
    } catch (err) {
        console.error('❌ Stop stream error:', err);
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

        if (startBtn) startBtn.addEventListener('click', startStream);
        if (stopBtn) stopBtn.addEventListener('click', stopStream);
    }
});
