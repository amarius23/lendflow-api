<?php

namespace App\Http\Controllers;

use App\Http\Requests\NYTBestSellersRequest;
use App\Services\NYTBestSellersService;
use Illuminate\Http\JsonResponse;

class NYTBestSellersController extends Controller
{
    private NYTBestSellersService $service;

    public function __construct(NYTBestSellersService $service)
    {
        $this->service = $service;
    }

    public function getBestSellers(NYTBestSellersRequest $request): JsonResponse
    {
        $data = $this->service->getBestSellers($request->validated());

        return response()->json($data, isset($data['error']) ? 500 : 200);
    }
}
