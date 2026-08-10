<?php

namespace App\Http\Controllers;

use App\Services\VerifierDashboardService;
use Illuminate\Http\JsonResponse;

class VerifierDashboardController extends Controller
{
    public function __construct(private VerifierDashboardService $verifierDashboardService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->verifierDashboardService->summary());
    }
}
