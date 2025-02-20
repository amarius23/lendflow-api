<?php
use App\Http\Controllers\NYTBestSellersController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('nyt-api', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});

Route::middleware('throttle:nyt-api')->get('/best-sellers', [NYTBestSellersController::class, 'getBestSellers']);
