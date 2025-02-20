<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class NYTBestSellersTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_successful_response()
    {
        // Mock NYT API response
        Http::fake([
            'api.nytimes.com/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    ['title' => 'Mock Book', 'author' => 'John Doe']
                ]
            ], 200)
        ]);

        // Call the API
        $response = $this->getJson('/api/best-sellers?author=John%20Doe');

        // Assert response structure
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'OK',
                'results' => [
                    ['title' => 'Mock Book', 'author' => 'John Doe']
                ]
            ]);
    }

    public function test_validation_fails_for_invalid_isbn()
    {
        $response = $this->getJson('/api/best-sellers?isbn[]=123');

        $response->assertStatus(422) // Laravel returns 422 for validation errors
        ->assertJsonValidationErrors(['isbn.0']);
    }

    public function test_validation_fails_for_negative_offset()
    {
        $response = $this->getJson('/api/best-sellers?offset=-5');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['offset']);
    }

    public function test_api_handles_nyt_errors()
    {
        // Simulate NYT API failure
        Http::fake([
            'api.nytimes.com/*' => Http::response(['error' => 'NYT API is down'], 500)
        ]);

        $response = $this->getJson('/api/best-sellers?author=Test');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'NYT API Error'
            ]);
    }

    public function test_rate_limiting_works()
    {
        // Reset rate limiter before test
        RateLimiter::clear('nyt-api');

        // Hit the API 30 times (max limit)
        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/best-sellers');
        }

        // 31st request should be rate-limited (429)
        $response = $this->getJson('/api/best-sellers');

        $response->assertStatus(429)
            ->assertJson([
                'message' => 'Too Many Requests',
                'status' => 429
            ]);
    }
}
