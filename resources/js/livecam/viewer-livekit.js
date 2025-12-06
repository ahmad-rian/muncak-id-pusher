/**
 * LiveKit Viewer - MuncakID Livestreaming
 * 
 * Features:
 * - LiveKit SFU for video playback (ultra-low latency)
 * - Pusher for chat & metadata
 * - Mirror state sync
 * - Mid-stream join support (automatic)
 */

import { Room, RoomEvent, Track } from 'livekit-client';

const streamId = window.streamId;
const streamSlug = window.streamSlug;

let livekitRoom = null;
let hasJoined = false; // Track if viewer has joined (prevent double counting)

console.log('👁️ LiveKit Viewer starting...');
console.log('Stream ID:', streamId);
console.log('Stream Slug:', streamSlug);

// Pusher setup
const pusher = window.pusher || new Pusher(window.pusherConfig.key, {
    cluster: window.pusherConfig.cluster,
    forceTLS: true
});

if (!window.pusher) {
    window.pusher = pusher;
}

const channel = pusher.subscribe(`stream.${streamId}`);
console.log('📡 Viewer subscribed to Pusher channel:', `stream.${streamId}`);

// DOM elements
const video = document.getElementById('video-player');
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-input');
const chatForm = document.getElementById('chat-form');
const charCounter = document.getElementById('char-counter');
const viewerCountEl = document.getElementById('viewer-count');

// Listen for mirror state changes (Pusher)
channel.bind('App\\Events\\MirrorStateChanged', (data) => {
    console.log('🪞 Mirror state changed:', data.is_mirrored);
    if (video) {
        video.style.transform = data.is_mirrored ? 'scaleX(-1)' : 'scaleX(1)';
    }
});

// Listen for orientation changes (Pusher)
channel.bind('App\\Events\\OrientationChanged', (data) => {
    console.log('📐 Orientation changed:', data);
    updateVideoOrientation(data.orientation, data.width, data.height);
});

// Update video container orientation
function updateVideoOrientation(orientation, width, height) {
    const videoContainer = video?.parentElement;
    if (!videoContainer) {
        console.warn('Video container not found');
        return;
    }

    // Remove existing aspect ratio classes
    videoContainer.classList.remove('aspect-video', 'aspect-[9/16]');

    if (orientation === 'portrait') {
        // Portrait mode - 9:16 aspect ratio
        videoContainer.classList.add('aspect-[9/16]');
        // On large screens, limit the width to prevent it from being too wide
        videoContainer.style.maxWidth = '600px';
        videoContainer.style.marginLeft = 'auto';
        videoContainer.style.marginRight = 'auto';
        console.log('📱 Switched to portrait mode (9:16)');
    } else {
        // Landscape mode - 16:9 aspect ratio (default)
        videoContainer.classList.add('aspect-video');
        videoContainer.style.maxWidth = '';
        videoContainer.style.marginLeft = '';
        videoContainer.style.marginRight = '';
        console.log('🖥️ Switched to landscape mode (16:9)');
    }
}

// Helper function to add chat message
function addChatMessage(username, message) {
    if (!chatMessages) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message px-3 py-2 rounded-lg bg-base-200 hover:bg-base-300 transition-colors';
    messageDiv.innerHTML = `<strong class="text-primary">${username}:</strong> <span class="text-base-content">${message}</span>`;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Listen for chat messages (Pusher)
channel.bind('App\\Events\\ChatMessageSent', (data) => {
    console.log('💬 Chat message:', data);
    addChatMessage(data.username, data.message);
});

// Listen for viewer count updates (Pusher)
channel.bind('App\\Events\\ViewerCountUpdated', (data) => {
    console.log('👥 Viewer count updated:', data.count);
    if (viewerCountEl) {
        viewerCountEl.textContent = data.count;
    }
    // Also update chat viewer count badge
    const chatViewerCount = document.getElementById('chat-viewer-count');
    if (chatViewerCount) {
        chatViewerCount.textContent = data.count;
    }
});

// Listen for stream status changes (Pusher)
channel.bind('App\\Events\\StreamStarted', () => {
    console.log('🟢 Stream started');
    initializeViewer();
});

channel.bind('App\\Events\\StreamEnded', () => {
    console.log('🔴 Stream ended');

    // Disconnect from LiveKit
    if (livekitRoom) {
        livekitRoom.disconnect();
        livekitRoom = null;
    }

    // Clear video
    if (video) {
        video.srcObject = null;
    }

    // Hide loading indicator
    const loadingIndicator = document.querySelector('.absolute.inset-0.flex.items-center.justify-center');
    if (loadingIndicator) {
        loadingIndicator.remove();
    }

    // Show stream ended message - find video container
    let videoContainer = null;
    if (video && video.parentElement) {
        videoContainer = video.parentElement;
    } else {
        // Fallback: find by class or ID
        videoContainer = document.querySelector('.relative.aspect-video') ||
            document.querySelector('#video-container') ||
            document.querySelector('video')?.parentElement;
    }

    if (videoContainer) {
        videoContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full bg-base-200 rounded-lg">
                <div class="text-center p-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto mb-4 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <h2 class="text-2xl font-bold mb-2">Stream Ended</h2>
                    <p class="text-base-content/70 mb-4">The broadcaster has ended the stream</p>
                    <p class="text-sm text-base-content/50">Redirecting to streams list in <span id="countdown">3</span>s...</p>
                    <div class="loading loading-spinner loading-md mt-4"></div>
                </div>
            </div>
        `;

        // Countdown timer
        let countdown = 3;
        const countdownEl = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownEl) {
                countdownEl.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    // Redirect to index after 3 seconds
    setTimeout(() => {
        window.location.href = '/live-cam';
    }, 3000);
});

// Initialize viewer
async function initializeViewer() {
    try {
        console.log('🎬 Initializing LiveKit viewer...');

        // Get LiveKit token
        const tokenResponse = await fetch(`/live-cam/${streamSlug}/livekit/token`);
        const tokenData = await tokenResponse.json();

        if (!tokenData.success) {
            throw new Error('Failed to get LiveKit token');
        }

        console.log('✅ Got LiveKit token');
        console.log('🔗 Connecting to:', tokenData.url);
        console.log('🏠 Room:', tokenData.room);

        // Create LiveKit room
        livekitRoom = new Room({
            adaptiveStream: true,
            dynacast: true,
        });

        // Setup event listeners
        livekitRoom.on(RoomEvent.Connected, () => {
            console.log('✅ Connected to LiveKit room');
        });

        livekitRoom.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            console.log('📺 Track subscribed:', track.kind, 'from', participant.identity);

            if (track.kind === Track.Kind.Video && video) {
                track.attach(video);
                video.play();
                console.log('✅ Video playing');

                // Hide loading indicator
                const loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
            }
        });

        livekitRoom.on(RoomEvent.TrackUnsubscribed, (track) => {
            console.log('📴 Track unsubscribed:', track.kind);
            track.detach();
        });

        livekitRoom.on(RoomEvent.Disconnected, () => {
            console.log('🔌 Disconnected from LiveKit room');
        });

        // Connect to room
        await livekitRoom.connect(tokenData.url, tokenData.token);

        // Update viewer count
        await updateViewerCount(1);
        hasJoined = true; // Mark as joined

    } catch (err) {
        console.error('❌ Failed to initialize viewer:', err);
    }
}

// Update viewer count
async function updateViewerCount(delta) {
    try {
        const action = delta > 0 ? 'join' : 'leave';
        const url = `/live-cam/${streamSlug}/viewer-count`;
        const data = JSON.stringify({ action });

        // Use sendBeacon for leave (more reliable on page unload)
        if (action === 'leave') {
            const blob = new Blob([data], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        } else {
            // Use fetch for join
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });
        }
    } catch (err) {
        console.error('Failed to update viewer count:', err);
    }
}

// Chat functionality
// (Variables already declared at top of file)

// Update character counter
if (chatInput && charCounter) {
    chatInput.addEventListener('input', () => {
        charCounter.textContent = `${chatInput.value.length}/200`;
    });
}

// Send chat message
if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const message = chatInput.value.trim();
        if (!message) return;

        // Get username from window or generate guest name
        const username = window.chatUsername || window.username || `Guest-${Math.random().toString(36).substr(2, 6).toUpperCase()}`;

        try {
            const response = await fetch(`/live-cam/${streamSlug}/chat`, {
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
                chatInput.value = '';
                charCounter.textContent = '0/200';
            }
        } catch (err) {
            console.error('Failed to send message:', err);
        }
    });
}

// Load chat history
async function loadChatHistory() {
    try {
        const response = await fetch(`/live-cam/${streamSlug}/chat-history`);
        const data = await response.json();

        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                addChatMessage(msg.username, msg.message);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
            console.log(`💬 Loaded ${data.messages.length} messages`);
        }
    } catch (err) {
        console.error('Failed to load chat history:', err);
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (livekitRoom) {
        updateViewerCount(-1);
        livekitRoom.disconnect();
    }
});

// Initialize
console.log('✅ LiveKit Viewer initialized');

// Auto-start if stream is live
(async () => {
    try {
        const response = await fetch(`/live-cam/${streamSlug}/status`);
        const data = await response.json();

        if (data.is_live) {
            console.log('🟢 Stream is LIVE');
            await initializeViewer();
        } else {
            console.log('⚪ Stream is OFFLINE');
        }
    } catch (err) {
        console.error('Failed to check stream status:', err);
    }
})();

// Load chat history
loadChatHistory();

// Pusher connection status
pusher.connection.bind('connected', () => {
    console.log('✅ Connected to Pusher');
});
