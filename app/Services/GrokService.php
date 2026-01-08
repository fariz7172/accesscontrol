<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GROK_API_KEY');
        $this->baseUrl = 'https://api.x.ai/v1/chat/completions';
    }

    public function query($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl, [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an assistant that provides Eloquent query suggestions for a Laravel application based on the provided database context and user prompt. Return only the suggested query or explanation, without additional text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'model' => 'grok-4-latest',
                'stream' => false,
                'temperature' => 0
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return ['response' => $data['choices'][0]['message']['content']];
            }

            Log::error('Grok API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return ['error' => 'Grok API request failed: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('Grok API exception', ['error' => $e->getMessage()]);
            return ['error' => 'Grok API exception: ' . $e->getMessage()];
        }
    }
}
