/**
 * MediaSource-based Live Streaming Viewer
 * Progressive chunk loading for scalable live streaming
 */

const streamId = window.streamId;
const streamSlug = window.streamSlug;
let mediaSource = null;
let sourceBuffer = null;
let sbErrorRetries = 0;
let queue = [];
let appendQueue = []; // Proper declaration to avoid ReferenceError
let isUpdating = false;
let lastChunkIndex = -1;
let fetchInterval = null;
let isStreamActive = false;
let isFetchingChunk = false;
let pendingChunks = new Set(); // Track chunks being fetched
let retryCounts = new Map(); // Retry counters per chunk index
window.initChunkAppended = false;

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
    if (!isStreamActive) return;
    if (lastChunkIndex === -1) {
        if (!pendingChunks.has(0)) {
            fetchAndAppendChunk(0);
        }
        return;
    }
    const nextExpected = lastChunkIndex + 1;
    if (data.index >= nextExpected && !pendingChunks.has(nextExpected)) {
        fetchAndAppendChunk(nextExpected);
    }
});

// Listen for stream status changes
channel.bind('stream-started', async () => {
    console.log('🎬 Stream started - cleaning up old stream data');

    // Stop any ongoing fetches
    isStreamActive = false;

    // Complete cleanup of old MediaSource and buffers (async)
    await cleanupMediaSource();

    // Reset all state
    lastChunkIndex = -1;
    queue = [];
    pendingChunks.clear();
    sbErrorRetries = 0;

    // Small delay to ensure cleanup is complete
    setTimeout(() => {
        isStreamActive = true;
        initializeMediaSource();
    }, 100);
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
async function cleanupMediaSource() {
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

    // Remove SourceBuffer event listeners and buffered data
    if (sourceBuffer) {
        try {
            // Wait for any pending updates
            if (sourceBuffer && sourceBuffer.updating) {
                await new Promise(resolve => {
                    const checkUpdate = setInterval(() => {
                        if (!sourceBuffer || !sourceBuffer.updating) {
                            clearInterval(checkUpdate);
                            resolve();
                        }
                    }, 100);

                    // Timeout after 2 seconds
                    setTimeout(() => {
                        clearInterval(checkUpdate);
                        resolve();
                    }, 2000);
                });
            }

            // Remove buffered data
            if (sourceBuffer && mediaSource && mediaSource.readyState === 'open') {
                try {
                    const buffered = sourceBuffer.buffered;
                    if (buffered.length > 0) {
                        const start = buffered.start(0);
                        const end = buffered.end(buffered.length - 1);
                        console.log(`🗑️ Removing buffered data from ${start} to ${end}`);

                        if (!sourceBuffer.updating) {
                            sourceBuffer.remove(start, end);

                            // Wait for remove to complete
                            await new Promise(resolve => {
                                if (sourceBuffer) {
                                    sourceBuffer.addEventListener('updateend', resolve, { once: true });
                                    setTimeout(resolve, 1000); // Timeout
                                } else {
                                    resolve();
                                }
                            });
                        }
                    }
                } catch (err) {
                    console.log('Error removing buffer:', err.message);
                }
            }
        } catch (err) {
            console.log('Could not remove buffer:', err.message);
        }
    }

    // Close MediaSource
    if (mediaSource) {
        try {
            if (mediaSource.readyState === 'open' && sourceBuffer && !sourceBuffer.updating) {
                mediaSource.endOfStream();
            }
        } catch (err) {
            console.log('MediaSource already closed:', err.message);
        }
    }

    // Clear video source and reset
    if (video) {
        video.pause();
        video.removeAttribute('src');
        video.load();
        video.currentTime = 0;
    }

    // Nullify references
    mediaSource = null;
    sourceBuffer = null;
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

        // Prevent multiple initialization
        if (sourceBuffer) {
            console.log('⚠️ SourceBuffer already exists, skipping initialization');
            return;
        }

        try {
            const mime = 'video/webm; codecs="vp8"';
            if (!window.MediaSource.isTypeSupported(mime)) {
                alert('WebM VP8 not supported in this browser');
                return;
            }

            sourceBuffer = mediaSource.addSourceBuffer(mime);
            sourceBuffer.mode = 'sequence';

            sourceBuffer.addEventListener('updateend', () => {
                isUpdating = false;
                processQueue();
            });

            sourceBuffer.addEventListener('error', async (e) => {
                console.error('❌ SourceBuffer error:', e);
                sbErrorRetries += 1;

                // Stop all ongoing operations
                isUpdating = false;

                // Reset state BEFORE cleanup to avoid race condition
                const wasActive = isStreamActive;
                appendQueue = [];
                pendingChunks.clear();
                lastChunkIndex = -1;

                // Clear intervals
                if (fetchInterval) {
                    clearInterval(fetchInterval);
                    fetchInterval = null;
                }

                // Cleanup properly (async)
                await cleanupMediaSource();

                // Nullify sourceBuffer after cleanup
                sourceBuffer = null;

                // Retry with exponential backoff
                if (sbErrorRetries <= 3 && wasActive) {
                    const delay = Math.pow(2, sbErrorRetries - 1) * 1000; // 1s, 2s, 4s
                    console.log(`🔄 Retrying MediaSource initialization in ${delay}ms (attempt ${sbErrorRetries}/3)`);
                    setTimeout(() => {
                        if (isStreamActive) {
                            initializeMediaSource();
                        }
                    }, delay);
                } else {
                    console.error('❌ Too many SourceBuffer errors, stopping stream');
                    isStreamActive = false;
                    if (loading) loading.classList.add('hidden');
                    if (offline) offline.classList.remove('hidden');
                }
            });

            // Hide loading, show video
            if (loading) loading.classList.add('hidden');
            if (offline) offline.classList.add('hidden');

            // Start fetching chunks
            startFetching();

        } catch (err) {
            console.error('❌ Failed to create SourceBuffer:', err);

            // Cleanup and retry
            sourceBuffer = null;
            if (sbErrorRetries < 3 && isStreamActive) {
                sbErrorRetries++;
                const delay = 1000 * sbErrorRetries;
                console.log(`🔄 Retrying after SourceBuffer creation error in ${delay}ms`);
                setTimeout(() => {
                    if (isStreamActive) {
                        initializeMediaSource();
                    }
                }, delay);
            } else {
                alert('Failed to initialize video player: ' + err.message);
            }
        }
    });

    mediaSource.addEventListener('sourceended', () => {
        console.log('🏁 MediaSource ended');
    });

    mediaSource.addEventListener('sourceclose', () => {
        console.log('🔌 MediaSource closed');
        sourceBuffer = null;
    });
}

// Start fetching chunks
function startFetching() {
    console.log('📡 Starting chunk fetching...');

    // Set up polling interval FIRST (before any early returns)
    // This ensures continuous chunk fetching even after chunk 0
    const pollingInterval = window.shouldSeekToLive ? 500 : 2000;

    fetchInterval = setInterval(() => {
        if (!isStreamActive || !mediaSource || mediaSource.readyState !== 'open') {
            clearInterval(fetchInterval);
            fetchInterval = null;
            return;
        }

        // Special handling for catch-up mode after chunk 0
        // Jump to recent chunks instead of sequentially fetching from chunk 1
        if (window.shouldSeekToLive && lastChunkIndex >= 0 && typeof window.fastStartIndex === 'number') {
            // Only jump if we haven't reached the fast start chunk yet
            if (lastChunkIndex < window.fastStartIndex && !pendingChunks.has(window.fastStartIndex)) {
                console.log(`⏩ Jumping from chunk ${lastChunkIndex} to chunk ${window.fastStartIndex} (catch-up mode)`);
                fetchAndAppendChunk(window.fastStartIndex);
                return;
            }
        }

        // Normal sequential fetching
        const nextIndex = lastChunkIndex + 1;
        if (!pendingChunks.has(nextIndex)) {
            fetchAndAppendChunk(nextIndex);
        }
    }, pollingInterval);

    // CRITICAL: Always fetch chunk 0 first (initialization segment)
    // WebM format requires init segment before any media chunks
    if (lastChunkIndex === -1 && !pendingChunks.has(0)) {
        console.log('🎬 Fetching initialization segment (chunk 0)...');
        fetchAndAppendChunk(0);
        // Don't return early - let the polling interval handle subsequent chunks
        return;
    }

    // If joining late, skip to recent chunks AFTER chunk 0 is loaded
    if (window.shouldSeekToLive && typeof window.latestChunkIndex === 'number' && window.latestChunkIndex > 20) {
        // Start from latest - 5 chunks for smooth playback
        const startChunk = Math.max(1, window.latestChunkIndex - 5); // Min 1, not 0
        console.log(`🚀 Catch-up mode: will fetch from chunk ${startChunk} (latest: ${window.latestChunkIndex}) after init segment`);

        // Fetch the start chunk after chunk 0 is processed
        if (lastChunkIndex >= 0 && !pendingChunks.has(startChunk)) {
            fetchAndAppendChunk(startChunk);
        }
    } else {
        // Normal start from beginning - fetch chunk 1 after chunk 0
        if (lastChunkIndex >= 0 && !pendingChunks.has(1)) {
            fetchAndAppendChunk(1);
        }
    }

    // During catch-up, fetch next chunks progressively
    if (window.shouldSeekToLive && lastChunkIndex >= 0) {
        // Fetch next 2 chunks (reduced from 3 to avoid race condition)
        setTimeout(() => {
            if (lastChunkIndex >= 0 && window.shouldSeekToLive) {
                for (let i = 1; i <= 2; i++) {
                    const nextIdx = lastChunkIndex + i;
                    if (!pendingChunks.has(nextIdx)) {
                        // Stagger the fetches to avoid overwhelming
                        setTimeout(() => fetchAndAppendChunk(nextIdx), i * 300);
                    }
                }
            }
        }, 800); // Increased delay to let initial chunk process first

        if (typeof window.fastStartIndex === 'number') {
            const fastStartCheck = setInterval(() => {
                if (!isStreamActive || !mediaSource || mediaSource.readyState !== 'open') {
                    clearInterval(fastStartCheck);
                    return;
                }
                if (window.initChunkAppended) {
                    clearInterval(fastStartCheck);
                    if (!pendingChunks.has(window.fastStartIndex)) {
                        fetchAndAppendChunk(window.fastStartIndex);
                        // Only fetch 1 chunk ahead (reduced from 2)
                        setTimeout(() => {
                            const idx = window.fastStartIndex + 1;
                            if (!pendingChunks.has(idx)) {
                                fetchAndAppendChunk(idx);
                            }
                        }, 500);
                    }
                }
            }, 300); // Increased interval for more stable checking
        }
    }
}

// Fetch and append chunk
async function fetchAndAppendChunk(index) {
    // Skip if stream is not active
    if (!isStreamActive) {
        console.log(`⏸️ Skipping chunk ${index} - stream not active`);
        return;
    }

    // Skip if we already have this chunk
    if (index <= lastChunkIndex) {
        console.log(`⏭️ Skipping chunk ${index} - already processed (last: ${lastChunkIndex})`);
        return;
    }

    // Skip if this chunk is already being fetched
    if (pendingChunks.has(index)) {
        return;
    }

    // Don't fetch chunks too far ahead (max 2 chunks ahead of last processed)
    // This prevents race condition where we try to fetch chunks not yet generated
    // EXCEPTION: Allow jumping to fastStartIndex in catch-up mode
    const isCatchupJump = window.shouldSeekToLive &&
        typeof window.fastStartIndex === 'number' &&
        index === window.fastStartIndex;

    if (lastChunkIndex >= 0 && index > lastChunkIndex + 2 && !isCatchupJump) {
        console.log(`⏸️ Chunk ${index} too far ahead (last: ${lastChunkIndex}), waiting...`);
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
            pendingChunks.delete(index);
            console.log(`⚠️ Chunk ${index} not available (${response.status})`);

            if (!isStreamActive) {
                return;
            }

            // Increase retry count to 5 and use longer delays
            const retryCount = retryCounts.get(index) || 0;
            if (retryCount < 5) {
                retryCounts.set(index, retryCount + 1);
                // Progressive delay: 1s, 2s, 3s, 4s, 5s
                const delay = 1000 * (retryCount + 1);
                console.log(`🔄 Will retry chunk ${index} in ${delay}ms (attempt ${retryCount + 1}/5)`);
                setTimeout(() => fetchAndAppendChunk(index), delay);
            } else {
                console.log(`❌ Giving up on chunk ${index} after 5 retries`);
                retryCounts.delete(index);
            }
            return;
        }

        const arrayBuffer = await response.arrayBuffer();

        if (arrayBuffer.byteLength === 0) {
            pendingChunks.delete(index);
            console.log(`⚠️ Chunk ${index} is empty`);

            // Don't retry if stream is not active
            if (!isStreamActive) {
                return;
            }

            setTimeout(() => fetchAndAppendChunk(index), 500);
            return;
        }

        console.log(`📦 Fetched chunk ${index}: ${(arrayBuffer.byteLength / 1024).toFixed(2)} KB`);

        // Double-check stream is still active before processing
        if (!isStreamActive) {
            console.log(`⏸️ Stream became inactive, discarding chunk ${index}`);
            pendingChunks.delete(index);
            return;
        }

        // Update last chunk index BEFORE adding to queue
        lastChunkIndex = index;

        pendingChunks.delete(index);
        retryCounts.delete(index);

        // Add to queue
        queue.push({
            index: index,
            data: arrayBuffer
        });

        processQueue();

    } catch (err) {
        pendingChunks.delete(index);
        console.log(`❌ Error fetching chunk ${index}:`, err.message);

        if (!isStreamActive) {
            return;
        }

        const retryCount = retryCounts.get(index) || 0;
        if (retryCount < 3) {
            retryCounts.set(index, retryCount + 1);
            setTimeout(() => fetchAndAppendChunk(index), 500 * (retryCount + 1));
        } else {
            retryCounts.delete(index);
        }
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

        if (chunk.index === 0) {
            window.initChunkAppended = true;
        }

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

        // Seek to live position if needed
        if (window.shouldSeekToLive && video.buffered.length > 0) {
            const bufferEnd = video.buffered.end(0);

            if (chunk.index >= 2 && bufferEnd >= 3) {
                const targetTime = Math.max(0, bufferEnd - 1);
                console.log(`⏩ Seeking to live position: ${targetTime.toFixed(1)}s (buffer end: ${bufferEnd.toFixed(1)}s)`);
                video.currentTime = targetTime;

                // Clear flag so we don't seek again
                window.shouldSeekToLive = false;
            }
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

            // Get latest chunk index to determine if we should seek to live
            const latestChunkIndex = data.latest_chunk_index || -1;

            if (latestChunkIndex >= 2) {
                // Stream has been running for a while
                // We'll fetch chunks quickly and then seek to live position
                console.log(`📍 Stream in progress (latest chunk: ${latestChunkIndex})`);
                console.log('📍 Will fetch chunks and seek to live position');

                // Store target time for seeking (we'll calculate it after chunks are loaded)
                window.shouldSeekToLive = true;
                window.latestChunkIndex = latestChunkIndex;
                window.fastStartIndex = Math.max(1, latestChunkIndex - 2);
            } else {
                // Stream just started, play from beginning
                console.log('📍 Starting from beginning (stream recently started)');
                window.shouldSeekToLive = false;
                window.fastStartIndex = undefined;
            }

            // Always start from chunk 0 (initialization segment)
            lastChunkIndex = -1;

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
