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
     * Receive captured frame from viewer dan proses classification
     */
    public function processFrame(Request $request, $streamId)
    {
        try {
            $request->validate([
                'image' => 'required|string', // base64 encoded image
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

            // Save image to public storage (untuk ditampilkan di index)
            $publicDir = public_path('storage/classifications');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            // Delete old classification image for this TRAIL (keep only latest per trail)
            $oldClassification = TrailClassification::where('hiking_trail_id', $stream->hiking_trail_id)
                ->latest('classified_at')
                ->first();

            if ($oldClassification && $oldClassification->image_path) {
                $oldPublicPath = public_path('storage/classifications/' . basename($oldClassification->image_path));
                if (file_exists($oldPublicPath)) {
                    @unlink($oldPublicPath);
                }
            }

            // Save new image dengan nama fixed per TRAIL (replace old image)
            $filename = 'trail_' . $stream->hiking_trail_id . '.' . $extension;
            $publicPath = $publicDir . '/' . $filename;
            file_put_contents($publicPath, $imageData);

            // Store relative path untuk database
            $imagePath = 'classifications/' . $filename;

            Log::info('Frame captured for classification', [
                'stream_id' => $streamId,
                'image_size' => strlen($imageData),
                'path' => $imagePath
            ]);

            // Create new classification record (processing status)
            $classification = TrailClassification::create([
                'live_stream_id' => $stream->id,
                'hiking_trail_id' => $stream->hiking_trail_id,
                'image_path' => $imagePath,
                'stream_delay_ms' => $request->input('delay_ms', 0),
                'status' => 'processing',
                'classified_at' => now(),
            ]);

            // Process classification dengan Gemini AI (async-like dengan queue lebih baik, tapi untuk demo kita sync)
            $result = $this->geminiService->classifyImage($publicPath);

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
                broadcast(new ClassificationReady($classification))->toOthers();

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
                    ]
                ]);
            } else {
                // Classification failed
                $classification->update([
                    'status' => 'failed',
                    'error_message' => 'Gemini AI classification failed',
                    'retry_count' => $classification->retry_count + 1,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Classification failed'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Frame processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Processing error: ' . $e->getMessage()
            ], 500);
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
