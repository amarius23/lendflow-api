<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NYTBestSellersService
{
    private string $apiUrl = "https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json";

    public function getBestSellers(array $params): array
    {
        $cacheKey = 'nyt_best_sellers_' . md5(json_encode($params));
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($params) {
            return $this->fetchFromAPI($params);
        });
    }

    private function fetchFromAPI(array $params): array
    {
        $queryParams = array_merge($params, ['api-key' => env('NYT_API_KEY')]);

        try {
            $response = Http::timeout(5)->get($this->apiUrl, $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'error' => 'NYT API Error',
                'details' => $response->json()
            ];
        } catch (\Throwable $e) {
            return [
                'error' => 'Request failed',
                'message' => $e->getMessage()
            ];
        }
    }
}
