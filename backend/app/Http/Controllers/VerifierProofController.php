<?php

namespace App\Http\Controllers;

use App\Services\VerifierProofService;
use Illuminate\Http\JsonResponse;

class VerifierProofController extends Controller
{
    public function __construct(private VerifierProofService $verifierProofService)
    {
    }

    public function show(int $proofId): JsonResponse
    {
        return response()->json($this->verifierProofService->show($proofId));
    }
}
