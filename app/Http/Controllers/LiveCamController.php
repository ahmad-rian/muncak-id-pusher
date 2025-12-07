<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\QualityChanged;
use App\Events\StreamEnded;
use App\Events\StreamStarted;
use App\Events\ViewerCountUpdated;
use App\Models\ChatMessage;
use App\Models\LiveStream;
use App\Models\StreamAnalytic;
use App\Models\TrailClassification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class LiveCamController extends Controller
{
    /**
     * Display a listing of live streams
     */
    public function index()
    {
        // Check if admin is accessing
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            // Admin view - show all streams
            $streams = LiveStream::with('hikingTrail.gunung', 'broadcaster')
                ->orderBy('status', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.live-stream.index', compact('streams'));
        }

        // Public view - only show live streams with latest classification
        $liveStreams = LiveStream::with(['hikingTrail.gunung', 'latestClassification'])
            ->live()
            ->orderBy('viewer_count', 'desc')
            ->get();

        $totalLive = $liveStreams->count();

        // Get latest classification per trail (cached for 5 minutes)
        $recentClassifications = Cache::remember('recent_trail_classifications', 300, function() {
            $classifications = TrailClassification::with(['liveStream.hikingTrail.gunung', 'hikingTrail'])
                ->where('status', 'completed')
                ->whereNotNull('hiking_trail_id')
                ->orderBy('classified_at', 'desc')
                ->limit(50) // Get more than needed
                ->get()
                ->unique('hiking_trail_id') // Only one per trail
                ->take(12);

            return $classifications;
        });

        // Get all trails that have classifications for filter (cached and optimized)
        $availableTrails = Cache::remember('available_trails_with_classifications', 300, function() {
            return TrailClassification::with('hikingTrail.gunung')
                ->where('status', 'completed')
                ->whereNotNull('hiking_trail_id')
                ->select('hiking_trail_id')
                ->distinct()
                ->get()
                ->pluck('hikingTrail')
                ->filter()
                ->sortBy('nama')
                ->values();
        });

        return view('live-cam.index', compact('liveStreams', 'totalLive', 'recentClassifications', 'availableTrails'));
    }

    /**
     * Show form to create new stream
     */
    public function create()
    {
        $hikingTrails = \App\Models\Rute::with('gunung.kabupatenKota.provinsi')
            ->orderBy('nama')
            ->get();

        return view('admin.live-stream.create', compact('hikingTrails'));
    }

    /**
     * Store new stream
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hiking_trail_id' => 'required|exists:rute,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quality' => 'sometimes|in:360p,720p,1080p',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $stream = LiveStream::create([
            'hiking_trail_id' => $request->hiking_trail_id,
            'broadcaster_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'offline',
            'current_quality' => $request->quality ?? '720p',
            'viewer_count' => 0,
        ]);

        return redirect()->route('admin.live-stream.broadcast', $stream->slug)
            ->with('success', 'Stream berhasil dibuat! Silakan setup kamera dan mulai siaran.');
    }

    /**
     * Show the live stream room
     */
    public function show(LiveStream $stream)
    {
        $stream->load(['hikingTrail.gunung', 'broadcaster']);

        // Generate anonymous username
        $username = session('livecam_username');
        if (!$username) {
            $username = 'Guest-' . strtoupper(substr(md5(uniqid()), 0, 6));
            session(['livecam_username' => $username]);
        }

        // Increment total views
        $stream->increment('total_views');

        return view('live-cam.show', compact('stream', 'username'));
    }

    /**
     * Show broadcaster dashboard
     */
    public function broadcast(LiveStream $stream)
    {
        $stream->load('hikingTrail.gunung');

        // Check if user is the broadcaster
        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            abort(403, 'Unauthorized - You are not the broadcaster of this stream');
        }

        return view('admin.live-stream.broadcast', compact('stream'));
    }

    /**
     * Start streaming
     */
    public function startStream(Request $request, LiveStream $stream)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user is the broadcaster
        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'quality' => 'sometimes|in:360p,720p,1080p',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        ChatMessage::where('live_stream_id', $stream->id)->delete();

        $this->cleanupAllChunks($stream->id);

        $stream->update([
            'status' => 'live',
            'started_at' => now(),
            'ended_at' => null,
            'current_quality' => $request->input('quality', '720p'),
            'viewer_count' => 0,
        ]);

        // Broadcast stream started event
        broadcast(new StreamStarted($stream))->toOthers();

        // Trigger immediate classification via HTTP (async)
        try {
            \Log::info('Triggering immediate classification for stream: ' . $stream->id);

            // Use HTTP client to call classification endpoint asynchronously
            \Illuminate\Support\Facades\Http::timeout(1)->async()->post(url("/api/v1/classifications/stream/{$stream->id}/classify"));

            \Log::info('Classification triggered for stream: ' . $stream->id);
        } catch (\Exception $e) {
            \Log::error('Failed to trigger classification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'stream' => $stream,
        ]);
    }

    /**
     * Stop streaming
     */
    public function stopStream(Request $request, LiveStream $stream)
    {
        \Log::info('Stop stream request received', ['stream_id' => $stream->id]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user is the broadcaster
        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            \Log::info('Updating stream status to offline', ['stream_id' => $stream->id]);

            // Update stream status
            try {
                $stream->update([
                    'status' => 'offline',
                    'ended_at' => now(),
                    'viewer_count' => 0,
                ]);
                \Log::info('Stream status updated successfully', ['stream_id' => $stream->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to update stream status', [
                    'stream_id' => $stream->id,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Re-throw to be caught by outer try-catch
            }

            // Broadcast stream ended event
            try {
                \Log::info('Broadcasting stream ended event', ['stream_id' => $stream->id]);
                broadcast(new StreamEnded($stream)); // Send to ALL (including viewers)
                \Log::info('Stream ended event broadcasted successfully', ['stream_id' => $stream->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast stream ended event', [
                    'stream_id' => $stream->id,
                    'error' => $e->getMessage()
                ]);
                // Continue even if broadcast fails
            }

            // Clean up Redis viewer tracking
            try {
                \Log::info('Cleaning up Redis viewer tracking', ['stream_id' => $stream->id]);
                Redis::del('stream:' . $stream->id . ':viewers');
                \Log::info('Redis cleanup completed', ['stream_id' => $stream->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to cleanup Redis', [
                    'stream_id' => $stream->id,
                    'error' => $e->getMessage()
                ]);
                // Continue even if Redis cleanup fails
            }

            // Clean up chunks
            try {
                \Log::info('Cleaning up chunks', ['stream_id' => $stream->id]);
                $this->cleanupAllChunks($stream->id);
                \Log::info('Chunks cleanup completed', ['stream_id' => $stream->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to cleanup chunks', [
                    'stream_id' => $stream->id,
                    'error' => $e->getMessage()
                ]);
                // Continue even if cleanup fails
            }

            \Log::info('Stop stream completed successfully', ['stream_id' => $stream->id]);

            return response()->json([
                'success' => true,
                'stream' => $stream,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to stop stream', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to stop stream',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send chat message
     */
    public function sendChat(Request $request, LiveStream $stream)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'message' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = $stream->id;

        if (!$stream->isLive()) {
            return response()->json(['error' => 'Stream is not live'], 400);
        }

        // Rate limiting: 100 messages per 10 seconds per IP (relaxed for load testing)
        $ip = $request->ip();
        $rateLimitKey = 'chat:ratelimit:' . $id . ':' . $ip;
        $messageCount = Cache::get($rateLimitKey, 0);

        if ($messageCount >= 100) {
            $ttl = Cache::get($rateLimitKey . ':ttl', 0);
            $waitTime = max(0, 10 - (time() - $ttl));
            return response()->json([
                'error' => 'Rate limit exceeded',
                'wait' => $waitTime
            ], 429);
        }

        // Sanitize message
        $message = strip_tags($request->input('message'));
        $username = strip_tags($request->input('username'));

        // Save to database
        ChatMessage::create([
            'live_stream_id' => $id,
            'username' => $username,
            'message' => $message,
        ]);

        // Update rate limit
        if ($messageCount === 0) {
            Cache::put($rateLimitKey . ':ttl', time(), 10);
        }
        Cache::put($rateLimitKey, $messageCount + 1, 10);

        // Broadcast chat message (now queued via Redis)
        event(new ChatMessageSent($id, $username, $message));

        return response()->json([
            'success' => true,
            'username' => $username,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get chat history for stream
     */
    public function getChatHistory(LiveStream $stream)
    {
        // Add pagination and limit - only get last 100 messages
        $messages = ChatMessage::where('live_stream_id', $stream->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'username' => $msg->username,
                    'message' => $msg->message,
                    'timestamp' => $msg->created_at->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Get recommended quality based on viewer count
     */
    public function getQuality(LiveStream $stream)
    {

        $viewerCount = $stream->viewer_count;

        // Quality recommendation logic
        $recommendedQuality = '720p';
        if ($viewerCount > 100) {
            $recommendedQuality = '360p';
        } elseif ($viewerCount > 50) {
            $recommendedQuality = '720p';
        } else {
            $recommendedQuality = '1080p';
        }

        return response()->json([
            'current_quality' => $stream->current_quality,
            'recommended_quality' => $recommendedQuality,
            'viewer_count' => $viewerCount,
        ]);
    }

    /**
     * Update viewer count
     */
    public function updateViewerCount(Request $request, LiveStream $stream)
    {
        $id = $stream->id;

        // Support both action-based and count-based updates
        if ($request->has('action')) {
            // Simple join/leave tracking
            $action = $request->input('action');

            if ($action === 'join') {
                $stream->increment('viewer_count');
            } elseif ($action === 'leave') {
                // Prevent negative count
                if ($stream->viewer_count > 0) {
                    $stream->decrement('viewer_count');
                } else {
                    $stream->update(['viewer_count' => 0]);
                }
            }

            // Refresh to get latest count
            $stream->refresh();

            // Broadcast viewer count update
            broadcast(new ViewerCountUpdated($id, $stream->viewer_count));

            return response()->json([
                'success' => true,
                'viewer_count' => $stream->viewer_count
            ]);
        }

        // Legacy count-based update
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newCount = $request->input('count');
        $oldCount = $stream->viewer_count;

        // Only update if difference > 5 viewers
        if (abs($newCount - $oldCount) >= 5) {
            $stream->update(['viewer_count' => $newCount]);

            // Save analytics
            StreamAnalytic::create([
                'live_stream_id' => $id,
                'timestamp' => now(),
                'viewer_count' => $newCount,
                'quality_level' => $stream->current_quality,
            ]);

            // Broadcast viewer count update
            broadcast(new ViewerCountUpdated($id, $newCount))->toOthers();
        }

        return response()->json([
            'success' => true,
            'viewer_count' => $newCount,
        ]);
    }

    /**
     * Change stream quality
     */
    public function changeQuality(Request $request, LiveStream $stream)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'quality' => 'required|in:360p,720p,1080p',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user is the broadcaster
        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $quality = $request->input('quality');
        $stream->update(['current_quality' => $quality]);

        // Broadcast quality change
        broadcast(new QualityChanged($stream->id, $quality))->toOthers();

        return response()->json([
            'success' => true,
            'quality' => $quality,
        ]);
    }

    /**
     * My broadcast - redirect to user's stream or create new one
     */
    public function myBroadcast()
    {
        if (!auth()->check()) {
            return redirect()->route('auth.sign-in')
                ->with('message', 'Silakan login untuk memulai broadcast');
        }

        // Get or create user's live stream
        $stream = LiveStream::firstOrCreate(
            ['broadcaster_id' => auth()->id()],
            [
                'title' => 'Live Stream - ' . auth()->user()->name,
                'description' => 'Live streaming from ' . auth()->user()->name,
                'status' => 'offline',
                'current_quality' => '720p',
            ]
        );

        return redirect()->route('live-cam.broadcast', $stream->slug);
    }

    /**
     * Update mirror state and broadcast to viewers
     */
    public function updateMirrorState(Request $request, LiveStream $stream)
    {
        $validator = Validator::make($request->all(), [
            'is_mirrored' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Broadcast mirror state to viewers
        broadcast(new \App\Events\MirrorStateChanged($stream->id, $request->input('is_mirrored')));

        return response()->json([
            'success' => true,
            'is_mirrored' => $request->input('is_mirrored')
        ]);
    }

    /**
     * Update video orientation and broadcast to viewers
     */
    public function updateOrientation(Request $request, LiveStream $stream)
    {
        $validator = Validator::make($request->all(), [
            'orientation' => 'required|in:landscape,portrait',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Broadcast orientation to viewers
        broadcast(new \App\Events\OrientationChanged(
            $stream->id,
            $request->input('orientation'),
            $request->input('width'),
            $request->input('height')
        ));

        return response()->json([
            'success' => true,
            'orientation' => $request->input('orientation'),
            'width' => $request->input('width'),
            'height' => $request->input('height')
        ]);
    }

    /**
     * Upload thumbnail for stream (captured once at start)
     */
    public function uploadThumbnail(Request $request, LiveStream $stream)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Decode base64 image
            $imageData = $request->input('image');

            // Remove data URL prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $extension = $matches[1];
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            } else {
                $extension = 'jpeg';
            }

            $decodedImage = base64_decode($imageData);

            if ($decodedImage === false) {
                return response()->json(['error' => 'Invalid image data'], 400);
            }

            // Save thumbnail
            $filename = 'stream_' . $stream->id . '_thumb.' . $extension;
            $path = 'thumbnails/' . $filename;

            // Store in public disk
            \Storage::disk('public')->put($path, $decodedImage);

            $stream->update([
                'thumbnail_url' => asset('storage/' . $path) . '?v=' . time()
            ]);

            return response()->json([
                'success' => true,
                'thumbnail_url' => asset('storage/' . $path)
            ]);

        } catch (\Exception $e) {
            \Log::error('Thumbnail upload failed', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a stream (admin only)
     */
    public function destroy(LiveStream $stream)
    {
        // Stop stream if it's live
        if ($stream->isLive()) {
            $stream->update([
                'status' => 'offline',
                'ended_at' => now(),
            ]);

            event(new StreamEnded($stream));
        }

        // Delete associated data
        $stream->chatMessages()->delete();
        $stream->analytics()->delete();
        $stream->delete();

        return redirect()->route('admin.live-stream.index')
            ->with('success', 'Stream berhasil dihapus');
    }

    /**
     * Viewer signals they are ready for WebRTC connection
     */
    public function viewerReady(Request $request, LiveStream $stream)
    {

        $viewerId = $request->input('viewer_id');

        \Log::info('Viewer ready received', [
            'stream_id' => $stream->id,
            'viewer_id' => $viewerId,
            'stream_status' => $stream->status
        ]);

        // Broadcast to channel that viewer is ready
        $event = new \App\Events\ViewerReady($stream->id, $viewerId);
        broadcast($event);

        \Log::info('ViewerReady event broadcasted', [
            'channel' => 'stream.' . $stream->id,
            'viewer_id' => $viewerId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Viewer ready event broadcasted',
            'stream_status' => $stream->status,
            'channel' => 'stream.' . $stream->id
        ]);
    }

    /**
     * Send WebRTC signal between broadcaster and viewer
     */
    public function sendSignal(Request $request, LiveStream $stream)
    {

        $viewerId = $request->input('viewer_id');
        $signal = $request->input('signal');
        $from = $request->input('from');

        \Log::info('WebRTC signal received', [
            'stream_id' => $stream->id,
            'viewer_id' => $viewerId,
            'from' => $from,
            'signal_type' => $signal['type'] ?? 'unknown'
        ]);

        // Just pass through - no server-side SDP manipulation
        if ($from === 'broadcaster') {
            $event = new \App\Events\WebRTCSignalBroadcaster($stream->id, $viewerId, $signal);
            broadcast($event);
            \Log::info('Broadcasted signal to viewer', ['viewer_id' => $viewerId]);
        } else {
            $event = new \App\Events\WebRTCSignalViewer($stream->id, $viewerId, $signal);
            broadcast($event);
            \Log::info('Broadcasted signal to broadcaster', ['viewer_id' => $viewerId]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Signal broadcasted',
            'from' => $from,
            'to' => $from === 'broadcaster' ? 'viewer' : 'broadcaster'
        ]);
    }

    /**
     * Broadcaster sends WebRTC offer to viewer
     */
    public function sendOffer(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        $viewerId = $request->input('viewer_id');
        $offer = $request->input('offer');

        // Fix SDP before broadcasting - ensure H.264 profile-level-id is valid
        if (isset($offer['sdp'])) {
            $offer['sdp'] = $this->fixSDPProfileLevelId($offer['sdp']);
        }

        // Broadcast offer to specific viewer via Pusher
        event(new \App\Events\WebRTCOffer($stream->id, $viewerId, $offer));

        return response()->json(['success' => true]);
    }

    /**
     * Clean SDP - Remove problematic lines that cause parsing errors
     * This is more aggressive than fixSDPProfileLevelId
     */
    private function cleanSDP($sdp)
    {
        $lines = explode("\r\n", $sdp);
        $cleanedLines = [];
        $removedCount = 0;

        // Track payload types to remove
        $payloadTypesToRemove = [];

        // First pass: identify problematic payload types
        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Find RED, RTX, ULPFEC payload types
            if (preg_match('/a=rtpmap:(\d+)\s+(red|rtx|ulpfec)/', $trimmed, $matches)) {
                $payloadTypesToRemove[] = $matches[1];
                \Log::info("🎯 Identified problematic codec: {$matches[2]} with payload type {$matches[1]}");
            }
        }

        // Second pass: clean lines and remove payload types from m= lines
        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines but keep them in output
            if (empty($trimmed)) {
                $cleanedLines[] = $line;
                continue;
            }

            // Remove problematic attribute lines
            $skipPatterns = [
                'a=ssrc:',              // Remove all SSRC lines (causes msid format issues)
                'a=ssrc-group:',        // Remove SSRC groups
                'a=msid-semantic:',     // Remove msid-semantic (not needed in unified-plan)
                'a=extmap:',            // Remove RTP header extensions
                'a=rtpmap:125',         // Remove payload 125 (usually ulpfec)
                'a=rtpmap:124',         // Remove payload 124 (usually rtx)
                'a=rtpmap:123',         // Remove payload 123 (usually red)
                'a=rtpmap:122',         // Remove payload 122 (usually rtx for red)
                'a=fmtp:125',           // Remove fmtp for 125
                'a=fmtp:124',           // Remove fmtp for 124
                'a=fmtp:123',           // Remove fmtp for 123
                'a=fmtp:122',           // Remove fmtp for 122
            ];

            $shouldSkip = false;
            foreach ($skipPatterns as $pattern) {
                if (str_starts_with($trimmed, $pattern)) {
                    \Log::info("🗑️ Removing problematic line: {$trimmed}");
                    $shouldSkip = true;
                    $removedCount++;
                    break;
                }
            }

            if (!$shouldSkip) {
                // Clean m= lines - remove problematic payload types
                if (preg_match('/^m=(audio|video)\s+(\d+)\s+(\S+)\s+(.+)/', $line, $matches)) {
                    $mediaType = $matches[1];
                    $port = $matches[2];
                    $proto = $matches[3];
                    $payloadTypes = explode(' ', $matches[4]);

                    // Filter out problematic payload types
                    $cleanPayloadTypes = array_filter($payloadTypes, function ($pt) use ($payloadTypesToRemove) {
                        return !in_array($pt, $payloadTypesToRemove);
                    });

                    if (count($cleanPayloadTypes) !== count($payloadTypes)) {
                        $removed = array_diff($payloadTypes, $cleanPayloadTypes);
                        \Log::info("🧹 Removed payload types from m={$mediaType} line: " . implode(', ', $removed));
                        $line = "m={$mediaType} {$port} {$proto} " . implode(' ', $cleanPayloadTypes);
                        $removedCount++;
                    }
                }

                // Fix profile-level-id if present
                if (str_contains($line, 'profile-level-id')) {
                    // Ensure it's valid 6-character hex
                    $line = preg_replace('/profile-level-id=([0-9a-fA-F]{1,5})(?![0-9a-fA-F])/', 'profile-level-id=42e01f', $line);
                    $line = preg_replace('/profile-level-id=64001f/', 'profile-level-id=42e01f', $line);
                }

                $cleanedLines[] = $line;
            }
        }

        $cleaned = implode("\r\n", $cleanedLines);

        \Log::info("✅ SDP cleaned successfully - removed/modified {$removedCount} lines");

        return $cleaned;
    }

    /**
     * Fix invalid profile-level-id in SDP
     * Ensures it's exactly 6 hex characters
     * Also removes problematic ssrc lines
     */
    private function fixSDPProfileLevelId($sdp)
    {
        // Pattern untuk mencari profile-level-id yang invalid (kurang dari 6 karakter)
        $pattern = '/profile-level-id=([0-9a-fA-F]{1,5})(?![0-9a-fA-F])/';

        // Replace dengan baseline profile yang compatible (42e01f = Baseline Profile Level 3.1)
        $fixed = preg_replace_callback($pattern, function ($matches) {
            $currentId = $matches[1];
            Log::info("🔧 Fixing invalid profile-level-id: {$currentId} -> 42e01f (baseline)");
            return 'profile-level-id=42e01f';
        }, $sdp);

        // Juga fix yang sudah 6 karakter tapi invalid format
        $fixed = preg_replace('/profile-level-id=64001f/', 'profile-level-id=42e01f', $fixed);

        // Remove ALL ssrc and ssrc-group lines (safe for unified-plan)
        // Fixes invalid msid format: a=ssrc:xxx msid:uuid uuid
        $lines = explode("\n", $fixed);
        $cleanedLines = array_filter($lines, function ($line) {
            $trimmed = trim($line);
            return !str_starts_with($trimmed, 'a=ssrc:') && !str_starts_with($trimmed, 'a=ssrc-group:');
        });
        $fixed = implode("\n", $cleanedLines);

        Log::info("🧹 Removed ssrc lines from SDP");

        // Fix fmtp parameter order - profile-level-id MUST be first for H.264
        // Chrome is strict about this order
        $fixed = preg_replace_callback(
            '/a=fmtp:(\d+)\s+(.+)/',
            function ($matches) {
                $payload = $matches[1];
                $params = $matches[2];

                // Check if has profile-level-id
                if (preg_match('/profile-level-id=([0-9a-fA-F]{6})/', $params, $profileMatch)) {
                    $profileId = $profileMatch[1];

                    // Remove profile-level-id from current position
                    $otherParams = preg_replace('/;?profile-level-id=[0-9a-fA-F]{6};?/', '', $params);
                    $otherParams = trim($otherParams, ';');

                    // Build new params with profile-level-id first
                    if (!empty($otherParams)) {
                        $newParams = "profile-level-id={$profileId};{$otherParams}";
                    } else {
                        $newParams = "profile-level-id={$profileId}";
                    }

                    Log::info("🔧 Reordered fmtp:{$payload} parameters - profile-level-id now first");
                    return "a=fmtp:{$payload} {$newParams}";
                }

                return $matches[0];
            },
            $fixed
        );

        return $fixed;
    }

    /**
     * Viewer sends WebRTC answer to broadcaster
     */
    public function sendAnswer(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        $viewerId = $request->input('viewer_id');
        $answer = $request->input('answer');

        // Broadcast answer to broadcaster via Pusher
        event(new \App\Events\WebRTCAnswer($stream->id, $viewerId, $answer));

        return response()->json(['success' => true]);
    }

    /**
     * Send ICE candidate (from broadcaster or viewer)
     */
    public function sendIceCandidate(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        $viewerId = $request->input('viewer_id');
        $candidate = $request->input('candidate');
        $fromBroadcaster = $request->input('from_broadcaster', false);

        if ($fromBroadcaster) {
            // From broadcaster to viewer
            event(new \App\Events\WebRTCIceCandidateBroadcaster($stream->id, $viewerId, $candidate));
        } else {
            // From viewer to broadcaster
            event(new \App\Events\WebRTCIceCandidate($stream->id, $viewerId, $candidate));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Upload video chunk from broadcaster (MSE streaming)
     */
    public function uploadChunk(Request $request, LiveStream $stream)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user is the broadcaster
        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'chunk' => 'required|file',
            'index' => 'required|integer|min:0',
            'timestamp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $chunkIndex = $request->input('index');
            $chunkFile = $request->file('chunk');

            // Create stream directory
            $streamDir = storage_path("app/live-streams/{$stream->id}");
            if (!file_exists($streamDir)) {
                mkdir($streamDir, 0755, true);
            }

            // Save chunk
            $chunkPath = "{$streamDir}/chunk_{$chunkIndex}.webm";
            $chunkFile->move($streamDir, "chunk_{$chunkIndex}.webm");

            Log::info("Chunk uploaded", [
                'stream_id' => $stream->id,
                'index' => $chunkIndex,
                'size' => filesize($chunkPath)
            ]);


            // Broadcast chunk availability (DISABLED - using LiveKit now)
            // event(new \App\Events\NewChunkAvailable($stream->id, $chunkIndex, '720p'));

            // Update stream metadata
            $stream->update([
                'latest_chunk_index' => $chunkIndex,
                'updated_at' => now()
            ]);

            // Cleanup old chunks (keep last 50)
            if ($chunkIndex > 50) {
                $oldChunkPath = "{$streamDir}/chunk_" . ($chunkIndex - 50) . ".webm";
                if (file_exists($oldChunkPath)) {
                    @unlink($oldChunkPath);
                }
            }

            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex
            ]);

        } catch (\Exception $e) {
            Log::error("Chunk upload failed", [
                'stream_id' => $stream->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to upload chunk'
            ], 500);
        }
    }

    /**
     * Serve video chunk to viewer
     */
    public function getChunk(Request $request, LiveStream $stream, $index)
    {
        // Prevent serving chunks if stream is not live
        if (!$stream->isLive()) {
            \Log::warning('Attempted to fetch chunk from non-live stream', [
                'stream_id' => $stream->id,
                'chunk_index' => $index,
                'stream_status' => $stream->status
            ]);
            return response('Stream is not live', 404);
        }

        $chunkPath = storage_path("app/live-streams/{$stream->id}/chunk_{$index}.webm");

        if (!file_exists($chunkPath)) {
            return response('', 404);
        }

        // Check if chunk is from current stream session
        $chunkModTime = filemtime($chunkPath);
        $streamStartTime = $stream->started_at ? $stream->started_at->timestamp : 0;

        // If chunk was created before stream started, it's from old session
        if ($chunkModTime < $streamStartTime) {
            \Log::warning('Chunk from old session requested', [
                'stream_id' => $stream->id,
                'chunk_index' => $index
            ]);
            return response('', 404);
        }

        return response()->file($chunkPath, [
            'Content-Type' => 'video/webm',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Get stream status
     */
    public function getStatus(LiveStream $stream)
    {
        $latestChunkIndex = -1;
        $oldestChunkIndex = -1;
        $hlsPlaylistUrl = null;

        // Get latest chunk index if stream is live
        if ($stream->isLive()) {
            $streamDir = storage_path("app/live-streams/{$stream->id}");
            if (file_exists($streamDir)) {
                $chunks = glob($streamDir . '/chunk_*.webm');
                if (!empty($chunks)) {
                    $streamStartTime = $stream->started_at ? $stream->started_at->timestamp : 0;
                    $chunks = array_filter($chunks, function ($path) use ($streamStartTime) {
                        return filemtime($path) >= $streamStartTime;
                    });

                    $indices = array_map(function ($path) {
                        preg_match('/chunk_(\d+)\.webm$/', $path, $matches);
                        return isset($matches[1]) ? (int) $matches[1] : -1;
                    }, $chunks);

                    // Filter valid indices
                    $indices = array_filter($indices, fn($i) => $i >= 0);

                    if (!empty($indices)) {
                        $latestChunkIndex = max($indices);

                        // Find oldest PLAYABLE chunk (exclude chunk 0 - init segment)
                        $playableIndices = array_filter($indices, fn($i) => $i > 0);
                        if (!empty($playableIndices)) {
                            $oldestChunkIndex = min($playableIndices);
                        } else {
                            // Only chunk 0 exists (stream just started)
                            $oldestChunkIndex = 0;
                        }
                    }
                }
            }

            // HLS playlist URL
            $hlsPlaylistUrl = url("/hls/{$stream->id}/master.m3u8");
        }

        return response()->json([
            'is_live' => $stream->isLive(),
            'latest_chunk_index' => $latestChunkIndex,
            'oldest_chunk_index' => $oldestChunkIndex, // Oldest PLAYABLE chunk
            'hls_playlist_url' => $hlsPlaylistUrl,
            'viewer_count' => $stream->viewer_count ?? 0,
        ]);
    }

    /**
     * Upload HLS chunk - Convert WebM to HLS segments
     */
    public function uploadHLSChunk(Request $request, LiveStream $stream)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'chunk' => 'required|file',
            'index' => 'required|integer|min:0',
            'timestamp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $chunkIndex = $request->input('index');
            $chunkFile = $request->file('chunk');

            $streamDir = storage_path("app/live-streams/{$stream->id}/hls");
            if (!file_exists($streamDir)) {
                mkdir($streamDir, 0755, true);
            }

            // Save WebM chunk temporarily
            $tempWebmPath = "{$streamDir}/temp_chunk_{$chunkIndex}.webm";
            $chunkFile->move(dirname($tempWebmPath), basename($tempWebmPath));

            // Convert to HLS segment using FFmpeg
            $segmentPath = "{$streamDir}/segment_{$chunkIndex}.ts";

            // FIXED FFmpeg command: Transcode VP8 → H.264 for HLS compatibility
            // -c:v libx264: Encode video to H.264
            // -preset ultrafast: Fastest encoding for live streaming
            // -tune zerolatency: Optimize for low latency
            // -c:a aac: Encode audio to AAC (HLS standard)
            // -f mpegts: Output format MPEG-TS
            $ffmpegCmd = sprintf(
                'ffmpeg -i %s -c:v libx264 -preset ultrafast -tune zerolatency -c:a aac -b:a 128k -f mpegts %s 2>&1',
                escapeshellarg($tempWebmPath),
                escapeshellarg($segmentPath)
            );

            exec($ffmpegCmd, $output, $returnCode);

            if ($returnCode !== 0) {
                Log::error("FFmpeg conversion failed", [
                    'stream_id' => $stream->id,
                    'index' => $chunkIndex,
                    'output' => implode("\n", array_slice($output, -10)) // Last 10 lines only
                ]);

                // Fallback: Save WebM directly (viewer will need HLS.js)
                copy($tempWebmPath, str_replace('.ts', '.webm', $segmentPath));
                $segmentPath = str_replace('.ts', '.webm', $segmentPath);
            }

            // Clean up temp file
            @unlink($tempWebmPath);

            Log::info("HLS segment created", [
                'stream_id' => $stream->id,
                'index' => $chunkIndex,
                'size' => filesize($segmentPath),
                'format' => pathinfo($segmentPath, PATHINFO_EXTENSION)
            ]);

            // Update stream metadata
            $stream->update([
                'latest_chunk_index' => $chunkIndex,
                'updated_at' => now()
            ]);

            // Cleanup old segments FIRST (keep last 30)
            $minSegmentToKeep = max(0, $chunkIndex - 30);
            // Clean both .ts and .webm files
            $allSegments = array_merge(
                glob("{$streamDir}/segment_*.ts"),
                glob("{$streamDir}/segment_*.webm")
            );
            foreach ($allSegments as $segmentPath) {
                if (preg_match('/segment_(\d+)\.(ts|webm)$/', $segmentPath, $matches)) {
                    $idx = (int) $matches[1];
                    if ($idx < $minSegmentToKeep) {
                        @unlink($segmentPath);
                        Log::info("Deleted old HLS segment", [
                            'stream_id' => $stream->id,
                            'segment_index' => $idx
                        ]);
                    }
                }
            }

            // THEN update playlist (after cleanup, so it only lists existing segments)
            $this->updateHLSPlaylist($stream, $chunkIndex);

            // Broadcast segment availability
            event(new \App\Events\NewHLSSegmentAvailable(
                $stream->id,
                $chunkIndex
            ));

            return response()->json([
                'success' => true,
                'chunk_index' => $chunkIndex
            ]);

        } catch (\Exception $e) {
            Log::error("HLS chunk upload failed", [
                'stream_id' => $stream->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to upload HLS chunk'
            ], 500);
        }
    }

    private function updateHLSPlaylist(LiveStream $stream, $latestChunkIndex)
    {
        $streamDir = storage_path("app/live-streams/{$stream->id}/hls");
        $playlistPath = "{$streamDir}/playlist.m3u8";

        // Get all available segments (both .ts and .webm)
        $segments = array_merge(
            glob("{$streamDir}/segment_*.ts"),
            glob("{$streamDir}/segment_*.webm")
        );
        $segmentIndices = [];

        foreach ($segments as $segmentPath) {
            if (preg_match('/segment_(\d+)\.(ts|webm)$/', basename($segmentPath), $matches)) {
                $index = (int) $matches[1];
                $ext = $matches[2];
                $segmentIndices[$index] = $ext; // Store extension
            }
        }

        ksort($segmentIndices); // Sort by index

        // Generate playlist
        $playlist = "#EXTM3U\n";
        $playlist .= "#EXT-X-VERSION:3\n";
        $playlist .= "#EXT-X-TARGETDURATION:2\n"; // 1-2 second segments
        $playlist .= "#EXT-X-MEDIA-SEQUENCE:" . (empty($segmentIndices) ? 0 : min(array_keys($segmentIndices))) . "\n";

        foreach ($segmentIndices as $index => $ext) {
            $playlist .= "#EXTINF:1.0,\n"; // 1 second duration
            $playlist .= "segment_{$index}.{$ext}\n"; // Use actual extension
        }

        file_put_contents($playlistPath, $playlist);

        Log::info("HLS playlist updated", [
            'stream_id' => $stream->id,
            'segments' => count($segmentIndices),
            'latest' => $latestChunkIndex
        ]);
    }

    /**
     * Serve HLS playlist
     */
    public function getHLSPlaylist(LiveStream $stream)
    {
        $playlistPath = storage_path("app/live-streams/{$stream->id}/hls/playlist.m3u8");

        if (!file_exists($playlistPath)) {
            return response('Playlist not found', 404);
        }

        return response(file_get_contents($playlistPath), 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    /**
     * Serve HLS segment or playlist
     */
    public function getHLSSegment(LiveStream $stream, $segmentName)
    {
        $segmentPath = storage_path("app/live-streams/{$stream->id}/hls/{$segmentName}");

        if (!file_exists($segmentPath)) {
            return response('Segment not found', 404);
        }

        // Determine content type based on file extension
        $contentType = 'video/webm'; // Default for .webm segments
        $cacheControl = 'public, max-age=31536000'; // Long cache for segments

        if (str_ends_with($segmentName, '.m3u8')) {
            $contentType = 'application/vnd.apple.mpegurl';
            $cacheControl = 'no-cache'; // Don't cache playlist - it updates frequently
        } elseif (str_ends_with($segmentName, '.ts')) {
            $contentType = 'video/MP2T';
        }

        return response(file_get_contents($segmentPath), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => $cacheControl,
            'Accept-Ranges' => 'bytes'
        ]);
    }

    private function cleanupAllChunks($streamId)
    {
        $streamDir = storage_path("app/live-streams/{$streamId}");
        if (!file_exists($streamDir)) {
            return;
        }

        // Clean WebM chunks
        $chunks = glob($streamDir . '/chunk_*.webm');
        foreach ($chunks as $chunkPath) {
            @unlink($chunkPath);
        }

        // Clean HLS segments
        $hlsDir = "{$streamDir}/hls";
        if (file_exists($hlsDir)) {
            $segments = glob($hlsDir . '/segment_*.ts');
            foreach ($segments as $segmentPath) {
                @unlink($segmentPath);
            }
            $playlist = "{$hlsDir}/playlist.m3u8";
            if (file_exists($playlist)) {
                @unlink($playlist);
            }
        }
    }

    /**
     * ========================================
     * LIVEKIT INTEGRATION METHODS
     * ========================================
     */

    /**
     * Generate LiveKit token for broadcaster
     */
    public function getLiveKitBroadcasterToken(Request $request, LiveStream $stream)
    {
        // Check authorization
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($stream->broadcaster_id && $stream->broadcaster_id !== auth()->id()) {
            return response()->json(['error' => 'Not authorized for this stream'], 403);
        }

        try {
            $livekitService = app(\App\Services\LiveKitService::class);

            $token = $livekitService->generateBroadcasterToken($stream, auth()->id());
            $serverUrl = $livekitService->getServerUrl();
            $roomName = $livekitService->getRoomName($stream);

            return response()->json([
                'success' => true,
                'token' => $token,
                'url' => $serverUrl,
                'room' => $roomName
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate broadcaster token', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate access token'
            ], 500);
        }
    }

    /**
     * Generate LiveKit token for viewer
     */
    public function getLiveKitViewerToken(Request $request, LiveStream $stream)
    {
        // Viewers don't need to be authenticated (public streams)
        // But we'll use their ID if they are logged in
        $userId = auth()->check() ? auth()->id() : null;

        try {
            $livekitService = app(\App\Services\LiveKitService::class);

            $token = $livekitService->generateViewerToken($stream, $userId);
            $serverUrl = $livekitService->getServerUrl();
            $roomName = $livekitService->getRoomName($stream);

            return response()->json([
                'success' => true,
                'token' => $token,
                'url' => $serverUrl,
                'room' => $roomName
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate viewer token', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate access token'
            ], 500);
        }
    }
}
