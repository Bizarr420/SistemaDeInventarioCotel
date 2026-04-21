<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SpringApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.spring.base_url');
        $this->apiKey = config('services.spring.api_key');
    }

    public function getMasterData(string $endpoint, array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/' . $endpoint, $params);

        if ($response->successful()) {
            return $response->json();
        }

        // Manejar errores
        throw new \Exception('Error fetching data from SPRING: ' . $response->body());
    }

    // Método específico para activos
    public function getAssets(): array
    {
        return $this->getMasterData('assets');
    }

    // No modificar datos en SPRING, solo consultar
}