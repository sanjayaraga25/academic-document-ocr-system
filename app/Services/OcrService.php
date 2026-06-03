<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ocr.base_url', 'http://localhost:8001');
    }

    public function process(string $filePath, string $originalName): array
    {
        $fullPath = Storage::disk('local')->path($filePath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("File not found: {$fullPath}");
        }

        try {
            $response = Http::timeout(120)
                ->attach('file', file_get_contents($fullPath), $originalName)
                ->post("{$this->baseUrl}/ocr");

            if ($response->failed()) {
                throw new RequestException($response);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('OCR Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function health(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
