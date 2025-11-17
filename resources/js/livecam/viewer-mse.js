/**
 * MediaSource-based Live Streaming Viewer
 * Progressive chunk loading for scalable live streaming
 */

const streamId = window.streamId;
let mediaSource = null;
let sourceBuffer = null;
let queue = [];
let isUpdating = false;
let lastChunkIndex = -1;
let fetchInterval = null;
let isStreamActive = false;

console.log('👁️ MSE Viewer starting...');
console.log('Stream ID:', streamId);

// Pusher configuration
const pusher = new Pusher(window.pusherConfig.key, {
    cluster: window.pusherConfig.cluster,
    forceTLS: true
});

const channel = pusher.subscribe(`stream.${streamId}`);
console.log('📡 Viewer subscribed to channel:', `stream.${streamId}`);

const video = document.getElementById('video-player');
const loading = document.getElementById('loading-indicator');
const offline = document.getElementById('offline-placeholder');

// Listen for new chunks via Pusher
channel.bind('new-chunk', (data) => {
    console.log('📨 New chunk available:', data.index);
    if (isStreamActive) {
        // Only fetch the next sequential chunk
        if (data.index === lastChunkIndex + 1) {
            fetchAndAppendChunk(data.index);
        }
    }
});

// Listen for stream status changes
channel.bind('stream-started', () => {
    console.log('🎬 Stream started');
    cleanupMediaSource(); // Cleanup old MediaSource first
    isStreamActive = true;
    lastChunkIndex = -1; // Reset chunk index
    queue = []; // Clear queue
    initializeMediaSource();
});

channel.bind('stream-ended', () => {
    console.log('🛑 Stream ended');
    isStreamActive = false;
    cleanupMediaSource();
    if (loading) loading.classList.add('hidden');
    if (offline) offline.classList.remove('hidden');
});

// Listen for viewer count updates
channel.bind('App\\Events\\ViewerCountUpdated', (data) => {
    console.log('👥 Viewer count updated:', data.count);

    // Update main viewer count badge
    const viewerCountEl = document.getElementById('viewer-count');
    if (viewerCountEl) {
        viewerCountEl.textContent = data.count;
    }

    // Update chat viewer count badge
    const chatViewerCountEl = document.getElementById('chat-viewer-count');
    if (chatViewerCountEl) {
        chatViewerCountEl.textContent = data.count;
    }
});

// Cleanup MediaSource
function cleanupMediaSource() {
    console.log('🧹 Cleaning up MediaSource...');

    // Stop fetch interval
    if (fetchInterval) {
        clearInterval(fetchInterval);
        fetchInterval = null;
    }

    // Clear queue
    queue = [];
    isUpdating = false;

    // Close MediaSource
    if (mediaSource) {
        try {
            if (mediaSource.readyState === 'open') {
                if (sourceBuffer && !sourceBuffer.updating) {
                    mediaSource.endOfStream();
                }
            }
        } catch (err) {
            console.log('MediaSource already closed');
        }
        mediaSource = null;
        sourceBuffer = null;
    }

    // Clear video source
    if (video) {
        video.src = '';
        video.load();
    }
}

// Initialize MediaSource
function initializeMediaSource() {
    if (!video) {
        console.error('❌ Video element not found');
        return;
    }

    if (!window.MediaSource) {
        console.error('❌ MediaSource not supported in this browser');
        alert('Your browser does not support live streaming. Please use a modern browser.');
        return;
    }

    console.log('🎬 Initializing MediaSource...');

    mediaSource = new MediaSource();
    video.src = URL.createObjectURL(mediaSource);

    mediaSource.addEventListener('sourceopen', () => {
        console.log('✅ MediaSource opened');

        try {
            // Use WebM with VP8 + Opus (same as broadcaster)
            sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8, opus"');

            sourceBuffer.addEventListener('updateend', () => {
                isUpdating = false;
                processQueue();
            });

            sourceBuffer.addEventListener('error', (e) => {
                console.error('❌ SourceBuffer error:', e);
                // Stop streaming on error
                isStreamActive = false;
                cleanupMediaSource();
            });

            // Hide loading, show video
            if (loading) loading.classList.add('hidden');
            if (offline) offline.classList.add('hidden');

            // Start fetching chunks
            startFetching();

        } catch (err) {
            console.error('❌ Failed to create SourceBuffer:', err);
            alert('Failed to initialize video player: ' + err.message);
        }
    });

    mediaSource.addEventListener('sourceended', () => {
        console.log('🏁 MediaSource ended');
    });

    mediaSource.addEventListener('sourceclose', () => {
        console.log('🔌 MediaSource closed');
    });
}

// Start fetching chunks
function startFetching() {
    console.log('📡 Starting chunk fetching...');

    // Fetch initial chunk
    fetchAndAppendChunk(0);

    // Polling fallback: check for next chunk if Pusher event is missed
    // This ensures we don't miss chunks even if network is slow
    const pollingInterval = setInterval(() => {
        if (!isStreamActive || !mediaSource || mediaSource.readyState !== 'open') {
            clearInterval(pollingInterval);
            return;
        }

        // Try to fetch the next expected chunk
        const nextIndex = lastChunkIndex + 1;
        fetchAndAppendChunk(nextIndex);
    }, 1200); // Poll every 1.2 seconds (slightly longer than 1s chunk interval)
}

// Fetch and append chunk
async function fetchAndAppendChunk(index) {
    // Skip if stream is not active
    if (!isStreamActive) {
        return;
    }

    // Skip if we already have this chunk
    if (index <= lastChunkIndex) {
        return;
    }

    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

    try {
        const response = await fetch(`${basePath}/chunk/${index}`);

        if (!response.ok) {
            // Chunk not available yet
            return;
        }

        const arrayBuffer = await response.arrayBuffer();

        if (arrayBuffer.byteLength === 0) {
            return;
        }

        console.log(`📦 Fetched chunk ${index}: ${(arrayBuffer.byteLength / 1024).toFixed(2)} KB`);

        // Add to queue
        queue.push({
            index: index,
            data: arrayBuffer
        });

        lastChunkIndex = index;
        processQueue();

    } catch (err) {
        // Silently fail - chunk might not be available yet
    }
}

// Process queue
function processQueue() {
    if (isUpdating || queue.length === 0) {
        return;
    }

    if (!sourceBuffer || sourceBuffer.updating) {
        return;
    }

    if (!mediaSource || mediaSource.readyState !== 'open') {
        return;
    }

    // Stop processing if stream is not active
    if (!isStreamActive) {
        return;
    }

    const chunk = queue.shift();

    try {
        isUpdating = true;
        sourceBuffer.appendBuffer(chunk.data);
        console.log(`✅ Appended chunk ${chunk.index}`);

        // Auto-play and unmute
        if (video.paused && video.readyState >= 2) {
            video.play().then(() => {
                // Unmute after successful play (browsers allow this)
                video.muted = false;
                console.log('🔊 Video playing with audio');
            }).catch(() => {
                console.log('Auto-play prevented, user interaction required');
            });
        }

    } catch (err) {
        console.error(`❌ Failed to append chunk ${chunk.index}:`, err);
        isUpdating = false;
    }
}

// Check stream status on load
async function checkStreamStatus() {
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

    try {
        const response = await fetch(`${basePath}/status`);
        const data = await response.json();

        if (data.is_live) {
            console.log('🟢 Stream is live');
            isStreamActive = true;
            initializeMediaSource();
        } else {
            console.log('⚫ Stream is offline');
            isStreamActive = false;
            if (loading) loading.classList.add('hidden');
            if (offline) offline.classList.remove('hidden');
        }
    } catch (err) {
        console.error('❌ Failed to check stream status:', err);
    }
}

// Send viewer join event
function joinStream() {
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

    fetch(`${basePath}/viewer-count`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action: 'join' })
    }).catch(err => console.error('Failed to join stream:', err));
}

// Send viewer leave event
function leaveStream() {
    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamId}`;

    fetch(`${basePath}/viewer-count`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action: 'leave' })
    }).catch(err => console.error('Failed to leave stream:', err));
}

// Initialize - wait for Pusher connection
channel.bind('pusher:subscription_succeeded', () => {
    console.log('✅ Connected to Pusher channel');
    checkStreamStatus();
    joinStream(); // Join when connected
});

// Leave when page unloads
window.addEventListener('beforeunload', () => {
    leaveStream();
});

// Setup chat functionality
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
const chatMessages = document.getElementById('chat-messages');

if (chatForm && chatInput) {
    // Prevent form reload
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // IMPORTANT: Prevent page reload!

        const message = chatInput.value.trim();
        if (!message) return;

        const username = window.chatUsername || 'Guest';

        try {
            const basePath = window.location.pathname.includes('/admin/live-stream')
                ? `/admin/live-stream/${streamId}`
                : `/live-cam/${streamId}`;

            const response = await fetch(`${basePath}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    username: username,
                    message: message
                })
            });

            if (response.ok) {
                chatInput.value = ''; // Clear input
            }
        } catch (err) {
            console.error('Failed to send chat:', err);
        }
    });

    // Character counter
    chatInput.addEventListener('input', () => {
        const counter = document.getElementById('char-counter');
        if (counter) {
            counter.textContent = `${chatInput.value.length}/200`;
        }
    });
}

// Listen for chat messages via Pusher
channel.bind('App\\Events\\ChatMessageSent', (data) => {
    console.log('💬 Chat message:', data);

    if (chatMessages) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'mb-2';
        messageDiv.innerHTML = `
            <div class="flex gap-2">
                <strong class="text-primary">${data.username}:</strong>
                <span>${data.message}</span>
            </div>
        `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});

console.log('✅ MSE Viewer initialized');
