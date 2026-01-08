<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GEMINI_API_KEY');
        $this->model = env('GEMINI_MODEL', 'gemini-1.5-flash'); // Default ke gemini-1.5-flash
    }

    public function query($prompt)
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API Key Gemini tidak dikonfigurasi di .env'];
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $this->apiKey, // Header sesuai contoh curl
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7, // Kontrol kreativitas (0-2)
                        'maxOutputTokens' => 1000, // Maksimal token output
                    ],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'response' => $body['candidates'][0]['content']['parts'][0]['text']
                ];
            } else {
                return ['error' => 'Respons tidak valid dari Gemini API'];
            }
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            return ['error' => 'Gagal request ke Gemini: ' . $errorBody];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
