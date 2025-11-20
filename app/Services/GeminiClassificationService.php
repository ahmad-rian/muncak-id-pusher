<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiClassificationService
{
    private string $apiKey;
    private string $model = 'gemini-2.0-flash-exp';
    private int $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    private function getApiUrl(): string
    {
        // Use v1beta API (same as React example)
        return "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * Classify image dengan Gemini AI
     *
     * @param string $imagePath Path ke image file
     * @return array|null Classification result atau null jika gagal
     */
    public function classifyImage(string $imagePath): ?array
    {
        try {
            // Check if file exists
            if (!file_exists($imagePath)) {
                Log::error('Image file not found', ['path' => $imagePath]);
                return null;
            }

            // Read and encode image to base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($imagePath);

            Log::info('Sending image to Gemini AI', [
                'path' => $imagePath,
                'size' => strlen($imageData),
                'mime' => $mimeType
            ]);

            // Prepare the prompt
            $prompt = $this->buildPrompt();

            // Call Gemini API with retry logic
            $response = $this->callGeminiApiWithRetry($base64Image, $mimeType, $prompt);

            if (!$response) {
                return null;
            }

            // Parse and validate response
            $classification = $this->parseResponse($response);

            Log::info('Gemini classification successful', ['classification' => $classification]);

            return $classification;

        } catch (\Exception $e) {
            Log::error('Gemini classification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Build prompt untuk Gemini AI
     */
    private function buildPrompt(): string
    {
        return <<<PROMPT
Analisis gambar jalur pendakian ini dan klasifikasikan menjadi 3 parameter:

1. CUACA (WEATHER): Pilih salah satu:
   - 'cerah': langit biru, sinar matahari terang, bayangan jelas
   - 'berawan': langit abu-abu, tidak ada bayangan keras, atmosfer berkabut
   - 'hujan': tetesan hujan terlihat, permukaan basah, langit gelap

2. KEPADATAN PENDAKI (CROWD): Hitung jumlah orang:
   - 'sepi': 0-2 orang terlihat
   - 'sedang': 3-10 orang terlihat
   - 'ramai': lebih dari 10 orang terlihat

3. VISIBILITAS: Assess jarak pandang:
   - 'jelas': objek jauh terlihat jelas, kontras tinggi
   - 'kabut_sedang': objek jauh sedikit kabur, kabut tipis
   - 'kabut_tebal': visibilitas di bawah 50m, dominan putih/abu-abu

PENTING: Kembalikan HANYA JSON dalam format ini (tidak ada teks tambahan):
{
  "weather": "cerah|berawan|hujan",
  "crowd": "sepi|sedang|ramai",
  "visibility": "jelas|kabut_sedang|kabut_tebal",
  "confidence": {
    "weather": 0.95,
    "crowd": 0.85,
    "visibility": 0.90
  }
}
PROMPT;
    }

    /**
     * Call Gemini API with retry logic
     */
    private function callGeminiApiWithRetry(string $base64Image, string $mimeType, string $prompt): ?array
    {
        $retries = 0;

        while ($retries < $this->maxRetries) {
            try {
                // Gemini API format
                $response = Http::timeout(30)
                    ->post($this->getApiUrl() . '?key=' . $this->apiKey, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $base64Image
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'maxOutputTokens' => 500,
                        ]
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('Gemini API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'retry' => $retries + 1
                ]);

            } catch (\Exception $e) {
                Log::error('Gemini API request exception', [
                    'error' => $e->getMessage(),
                    'retry' => $retries + 1
                ]);
            }

            $retries++;

            // Wait before retry (exponential backoff)
            if ($retries < $this->maxRetries) {
                sleep(pow(2, $retries)); // 2s, 4s, 8s
            }
        }

        return null;
    }

    /**
     * Parse Gemini response dan extract classification
     */
    private function parseResponse(array $response): ?array
    {
        try {
            // Extract content from Gemini response
            $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error('No content in Gemini response', ['response' => $response]);
                return null;
            }

            // Try to extract JSON from content (might have extra text)
            $content = trim($content);

            // Remove markdown code blocks if present
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*/', '', $content);
            $content = trim($content);

            // Try to decode directly first (Gemini usually returns clean JSON)
            $classification = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($classification)) {
                // Validate required fields
                if ($this->validateClassification($classification)) {
                    return $classification;
                }
            }

            // If direct decode failed, try to extract JSON object with regex
            // Use balanced braces pattern to handle nested objects
            if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $content, $matches)) {
                $jsonString = $matches[0];
                $classification = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($classification)) {
                    // Validate required fields
                    if ($this->validateClassification($classification)) {
                        return $classification;
                    }
                }
            }

            Log::error('Failed to parse JSON from Gemini response', ['content' => $content]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error parsing Gemini response', [
                'error' => $e->getMessage(),
                'response' => $response
            ]);
            return null;
        }
    }

    /**
     * Validate classification result
     */
    private function validateClassification(array $classification): bool
    {
        // Check required fields
        if (!isset($classification['weather']) ||
            !isset($classification['crowd']) ||
            !isset($classification['visibility'])) {
            return false;
        }

        // Validate weather values
        $validWeather = ['cerah', 'berawan', 'hujan'];
        if (!in_array($classification['weather'], $validWeather)) {
            return false;
        }

        // Validate crowd values
        $validCrowd = ['sepi', 'sedang', 'ramai'];
        if (!in_array($classification['crowd'], $validCrowd)) {
            return false;
        }

        // Validate visibility values
        $validVisibility = ['jelas', 'kabut_sedang', 'kabut_tebal'];
        if (!in_array($classification['visibility'], $validVisibility)) {
            return false;
        }

        return true;
    }

    /**
     * Test connection to Gemini API
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(10)
                ->get('https://generativelanguage.googleapis.com/v1beta/models?key=' . $this->apiKey);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gemini connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
