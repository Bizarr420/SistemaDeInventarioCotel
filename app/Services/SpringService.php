<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SpringService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.spring.base_url') ?? 'http://localhost:8080/api';
        $this->apiKey = config('services.spring.api_key');
    }

    public function getHttpClient()
    {
        $client = Http::baseUrl($this->baseUrl);

        if (!empty($this->apiKey)) {
            $client = $client->withToken($this->apiKey);
        }

        return $client;
    }

    public function fetchAssets(): array
    {
        if (empty($this->baseUrl)) {
            return [];
        }

        $response = $this->getHttpClient()->get('/assets');

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    public function fetchAsset(string $internalCode): ?array
    {
        if (empty($this->baseUrl)) {
            return null;
        }

        $response = $this->getHttpClient()->get('/assets/' . urlencode($internalCode));

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}