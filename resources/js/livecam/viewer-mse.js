/**
 * MediaSource-based Live Streaming Viewer
 * Progressive chunk loading for scalable live streaming
 */

const streamId = window.streamId;
const streamSlug = window.streamSlug;
let mediaSource = null;
let sourceBuffer = null;
let queue = [];
let isUpdating = false;
let lastChunkIndex = -1;
let fetchInterval = null;
let isStreamActive = false;
let isFetchingChunk = false;
let pendingChunks = new Set(); // Track chunks being fetched

console.log('👁️ MSE Viewer starting...');
console.log('Stream ID:', streamId);
console.log('Stream Slug:', streamSlug);

// Pusher configuration - use shared instance if available (dari trail-classifier.js)
const pusher = window.pusher || new Pusher(window.pusherConfig.key, {
    cluster: window.pusherConfig.cluster,
    forceTLS: true
});

// Make pusher globally available
if (!window.pusher) {
    window.pusher = pusher;
}

const channel = pusher.subscribe(`stream.${streamId}`);
console.log('📡 Viewer subscribed to channel:', `stream.${streamId}`);

const video = document.getElementById('video-player');
const loading = document.getElementById('loading-indicator');
const offline = document.getElementById('offline-placeholder');

// Listen for new chunks via Pusher
channel.bind('new-chunk', (data) => {
    console.log('📨 New chunk available:', data.index);
    if (isStreamActive) {
        // Always try to fetch the next expected chunk
        const nextExpected = lastChunkIndex + 1;
        if (data.index >= nextExpected && !pendingChunks.has(nextExpected)) {
            fetchAndAppendChunk(nextExpected);
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

// Listen for mirror state changes from broadcaster
channel.bind('App\\Events\\MirrorStateChanged', (data) => {
    console.log('🪞 Mirror state changed:', data.is_mirrored);
    const videoPlayer = document.getElementById('video-player');
    if (videoPlayer) {
        videoPlayer.style.transform = data.is_mirrored ? 'scaleX(-1)' : 'scaleX(1)';
        videoPlayer.style.transition = 'transform 0.3s ease';
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

    // Clear queue and flags
    queue = [];
    isUpdating = false;
    isFetchingChunk = false;
    pendingChunks.clear();

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
            // Use WebM with VP8 only (no audio)
            sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');

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
    fetchInterval = setInterval(() => {
        if (!isStreamActive || !mediaSource || mediaSource.readyState !== 'open') {
            clearInterval(fetchInterval);
            fetchInterval = null;
            return;
        }

        // Try to fetch the next expected chunk if not already fetching
        const nextIndex = lastChunkIndex + 1;
        if (!pendingChunks.has(nextIndex)) {
            fetchAndAppendChunk(nextIndex);
        }
    }, 2500); // Poll every 2.5 seconds (backup mechanism)
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

    // Skip if this chunk is already being fetched
    if (pendingChunks.has(index)) {
        return;
    }

    // Mark as pending
    pendingChunks.add(index);

    const basePath = window.location.pathname.includes('/admin/live-stream')
        ? `/admin/live-stream/${streamId}`
        : `/live-cam/${streamSlug}`;

    try {
        const response = await fetch(`${basePath}/chunk/${index}`);

        if (!response.ok) {
            // Chunk not available - skip to next
            pendingChunks.delete(index);
            console.log(`⚠️ Chunk ${index} not available (404), skipping...`);

            // Update lastChunkIndex to skip this chunk
            lastChunkIndex = index;

            // Try next chunk immediately
            setTimeout(() => fetchAndAppendChunk(index + 1), 100);
            return;
        }

        const arrayBuffer = await response.arrayBuffer();

        if (arrayBuffer.byteLength === 0) {
            pendingChunks.delete(index);
            lastChunkIndex = index;
            setTimeout(() => fetchAndAppendChunk(index + 1), 100);
            return;
        }

        console.log(`📦 Fetched chunk ${index}: ${(arrayBuffer.byteLength / 1024).toFixed(2)} KB`);

        // Update last chunk index BEFORE adding to queue
        lastChunkIndex = index;

        // Remove from pending
        pendingChunks.delete(index);

        // Add to queue
        queue.push({
            index: index,
            data: arrayBuffer
        });

        processQueue();

    } catch (err) {
        // Remove from pending on error
        pendingChunks.delete(index);
        console.log(`⚠️ Error fetching chunk ${index}, skipping...`);
        lastChunkIndex = index;
        setTimeout(() => fetchAndAppendChunk(index + 1), 100);
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

        // Auto-play as soon as possible
        if (video.paused) {
            // Try to play immediately after any chunk is appended
            // readyState check removed - let browser decide when it's ready
            video.play().then(() => {
                // Keep muted (no audio track)
                video.muted = true;
                console.log('📹 Video playing (video only) at chunk', chunk.index);
            }).catch(() => {
                // Silently fail - browser will play when ready
                // This is normal for the first few chunks
            });
        }

        // Monitor buffer health
        if (video.buffered.length > 0) {
            const bufferEnd = video.buffered.end(0);
            const currentTime = video.currentTime;
            const bufferAhead = bufferEnd - currentTime;

            // Log buffer status occasionally
            if (chunk.index % 5 === 0) {
                console.log(`📊 Buffer: ${bufferAhead.toFixed(1)}s ahead`);
            }
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
        : `/live-cam/${streamSlug}`;

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
        : `/live-cam/${streamSlug}`;

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
        : `/live-cam/${streamSlug}`;

    fetch(`${basePath}/viewer-count`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action: 'leave' })
    }).catch(err => console.error('Failed to leave stream:', err));
}

// Load chat history
async function loadChatHistory() {
    try {
        const basePath = window.location.pathname.includes('/admin/live-stream')
            ? `/admin/live-stream/${streamId}`
            : `/live-cam/${streamSlug}`;

        const response = await fetch(`${basePath}/chat-history`);
        const data = await response.json();

        if (data.success && data.messages && chatMessages) {
            // Clear existing messages
            chatMessages.innerHTML = '';

            // Add history messages
            data.messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'mb-2';
                messageDiv.innerHTML = `
                    <div class="flex gap-2">
                        <strong class="text-primary">${msg.username}:</strong>
                        <span>${msg.message}</span>
                    </div>
                `;
                chatMessages.appendChild(messageDiv);
            });

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            console.log(`💬 Loaded ${data.messages.length} chat messages from history`);
        }
    } catch (err) {
        console.error('Failed to load chat history:', err);
    }
}

// Initialize - wait for Pusher connection
channel.bind('pusher:subscription_succeeded', () => {
    console.log('✅ Connected to Pusher channel');
    checkStreamStatus();
    joinStream(); // Join when connected
    loadChatHistory(); // Load chat history
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
                : `/live-cam/${streamSlug}`;

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
