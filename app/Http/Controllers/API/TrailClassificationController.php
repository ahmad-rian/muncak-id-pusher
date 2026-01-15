<?php

namespace App\Http\Controllers\API;

use App\Events\ClassificationReady;
use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\TrailClassification;
use App\Services\GeminiClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrailClassificationController extends Controller
{
    private GeminiClassificationService $geminiService;

    public function __construct(GeminiClassificationService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Receive captured frame and video from viewer dan proses classification
     * Video akan di-overwrite setiap 30 menit untuk menghemat storage
     * Gambar tetap di-capture untuk klasifikasi tapi tidak ditampilkan di frontend
     */
    public function processFrame(Request $request, $streamId)
    {
        try {
            $request->validate([
                'image' => 'required|string', // base64 encoded image for AI classification
                'video' => 'nullable|string', // base64 encoded video (5 seconds)
                'video_duration' => 'nullable|integer|max:10', // max 10 seconds
                'delay_ms' => 'nullable|integer',
            ]);

            // Get live stream
            $stream = LiveStream::with('hikingTrail')->findOrFail($streamId);

            // Check if stream is live
            if ($stream->status !== 'live') {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream is not live'
                ], 400);
            }

            // Check if stream has hiking trail (required for classification)
            if (!$stream->hiking_trail_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stream does not have a hiking trail assigned'
                ], 400);
            }

            // Decode base64 image
            $imageData = $request->input('image');

            // Remove data:image/...;base64, prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $extension = $matches[1];
            } else {
                $extension = 'jpg';
            }

            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data'
                ], 400);
            }

            // Save image to public storage (untuk klasifikasi AI, tidak ditampilkan)
            $publicDir = public_path('storage/classifications');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            // Video directory
            $videoDir = public_path('storage/classification_videos');
            if (!file_exists($videoDir)) {
                mkdir($videoDir, 0755, true);
            }

            // Delete old classification files for this TRAIL (keep only latest per trail)
            $oldClassification = TrailClassification::where('hiking_trail_id', $stream->hiking_trail_id)
                ->latest('classified_at')
                ->first();

            if ($oldClassification) {
                // Delete old image if exists
                if ($oldClassification->image_path) {
                    $oldImagePath = public_path('storage/' . $oldClassification->image_path);
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                // Delete old video if exists
                if ($oldClassification->video_path) {
                    $oldVideoPath = public_path('storage/' . $oldClassification->video_path);
                    if (file_exists($oldVideoPath)) {
                        @unlink($oldVideoPath);
                    }
                }
            }

            // Save new image dengan nama fixed per TRAIL (replace old image)
            $imageFilename = 'trail_' . $stream->hiking_trail_id . '.' . $extension;
            $imagePath = $publicDir . '/' . $imageFilename;
            file_put_contents($imagePath, $imageData);

            // Store relative path untuk database
            $imageRelativePath = 'classifications/' . $imageFilename;

            // Process video if provided
            $videoRelativePath = null;
            $videoDuration = null;

            if ($request->has('video') && $request->input('video')) {
                $videoData = $request->input('video');

                // Remove data:video/...;base64, prefix if present
                // Handle complex MIME types like "video/webm;codecs=vp9"
                if (preg_match('/^data:video\/([^;,]+)[^,]*;base64,/', $videoData, $videoMatches)) {
                    $videoData = substr($videoData, strpos($videoData, ',') + 1);
                    $mimeType = strtolower($videoMatches[1]);
                    $videoExtension = str_contains($mimeType, 'webm') ? 'webm' : 'mp4';
                } else {
                    $videoExtension = 'webm';
                }

                // Validate base64 data - remove any whitespace that might have been added
                $videoData = preg_replace('/\s+/', '', $videoData);
                $videoData = base64_decode($videoData, true); // strict mode

                if ($videoData !== false && strlen($videoData) > 100) {
                    // Verify it's a valid video file by checking magic bytes
                    $header = substr($videoData, 0, 4);
                    $isWebm = ($header === "\x1a\x45\xdf\xa3"); // EBML header for WebM
                    $isMp4 = (substr($videoData, 4, 4) === "ftyp"); // MP4 signature

                    if (!$isWebm && !$isMp4) {
                        Log::warning('Video data does not appear to be a valid video file', [
                            'trail_id' => $stream->hiking_trail_id,
                            'header_hex' => bin2hex($header),
                            'data_size' => strlen($videoData)
                        ]);
                    }

                    // Save video dengan nama fixed per TRAIL (overwrite old video)
                    $videoFilename = 'trail_' . $stream->hiking_trail_id . '.' . $videoExtension;
                    $videoPath = $videoDir . '/' . $videoFilename;
                    file_put_contents($videoPath, $videoData);

                    $videoRelativePath = 'classification_videos/' . $videoFilename;
                    $videoDuration = $request->input('video_duration', 5);

                    Log::info('Video saved for trail classification', [
                        'trail_id' => $stream->hiking_trail_id,
                        'video_path' => $videoRelativePath,
                        'video_size' => strlen($videoData),
                        'duration' => $videoDuration
                    ]);
                }
            }

            Log::info('Frame captured for classification', [
                'stream_id' => $streamId,
                'image_size' => strlen($imageData),
                'path' => $imageRelativePath,
                'has_video' => !is_null($videoRelativePath)
            ]);

            // Create new classification record (processing status)
            $classification = TrailClassification::create([
                'live_stream_id' => $stream->id,
                'hiking_trail_id' => $stream->hiking_trail_id,
                'image_path' => $imageRelativePath,
                'video_path' => $videoRelativePath,
                'video_duration' => $videoDuration,
                'stream_delay_ms' => $request->input('delay_ms', 0),
                'status' => 'processing',
                'classified_at' => now(),
            ]);

            // Process classification dengan Gemini AI (menggunakan gambar)
            $result = $this->geminiService->classifyImage($imagePath);

            if ($result) {
                // Update classification dengan hasil
                $classification->update([
                    'weather' => $result['weather'],
                    'crowd' => $result['crowd'],
                    'visibility' => $result['visibility'],
                    'weather_confidence' => $result['confidence']['weather'] ?? 0,
                    'crowd_confidence' => $result['confidence']['crowd'] ?? 0,
                    'visibility_confidence' => $result['confidence']['visibility'] ?? 0,
                    'status' => 'completed',
                ]);

                Log::info('Classification completed', [
                    'stream_id' => $streamId,
                    'result' => $result
                ]);

                // Broadcast classification ready event to viewers
                broadcast(new ClassificationReady($classification)); // Send to ALL viewers

                return response()->json([
                    'success' => true,
                    'message' => 'Classification completed',
                    'data' => [
                        'id' => $classification->id,
                        'weather' => $classification->weather,
                        'crowd' => $classification->crowd,
                        'visibility' => $classification->visibility,
                        'weather_label' => $classification->weather_label,
                        'crowd_label' => $classification->crowd_label,
                        'visibility_label' => $classification->visibility_label,
                        'recommendation' => $classification->recommendation,
                        'classified_at' => $classification->classified_at->format('H:i:s'),
                        'has_video' => !is_null($videoRelativePath),
                        'video_path' => $videoRelativePath ? asset('storage/' . $videoRelativePath) : null,
                    ]
                ]);
            } else {
                $classification->update([
                    'status' => 'failed',
                    'error_message' => 'Gemini AI classification failed',
                    'retry_count' => $classification->retry_count + 1,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Classification failed'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Frame processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Processing error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get latest classification untuk stream
     */
    public function getLatest($streamId)
    {
        try {
            $classification = TrailClassification::getLatestForStream($streamId);

            if (!$classification) {
                return response()->json([
                    'success' => false,
                    'message' => 'No classification data available'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $classification->id,
                    'weather' => $classification->weather,
                    'crowd' => $classification->crowd,
                    'visibility' => $classification->visibility,
                    'weather_label' => $classification->weather_label,
                    'crowd_label' => $classification->crowd_label,
                    'visibility_label' => $classification->visibility_label,
                    'recommendation' => $classification->recommendation,
                    'confidence' => [
                        'weather' => $classification->weather_confidence,
                        'crowd' => $classification->crowd_confidence,
                        'visibility' => $classification->visibility_confidence,
                    ],
                    'classified_at' => $classification->classified_at->format('d M Y H:i:s'),
                    'classified_at_human' => $classification->classified_at->diffForHumans(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get classifications by hiking trail (untuk filter)
     */
    public function getByTrail($trailId)
    {
        try {
            $classifications = TrailClassification::where('hiking_trail_id', $trailId)
                ->where('status', 'completed')
                ->with(['liveStream', 'hikingTrail'])
                ->orderBy('classified_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $classifications->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'stream_title' => $c->liveStream->title,
                        'trail_name' => $c->hikingTrail->nama,
                        'weather' => $c->weather,
                        'crowd' => $c->crowd,
                        'visibility' => $c->visibility,
                        'weather_label' => $c->weather_label,
                        'crowd_label' => $c->crowd_label,
                        'visibility_label' => $c->visibility_label,
                        'recommendation' => $c->recommendation,
                        'classified_at' => $c->classified_at->format('d M Y H:i:s'),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
