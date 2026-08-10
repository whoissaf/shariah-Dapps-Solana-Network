<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationReadRequest;
use App\Http\Requests\VerifyProofRequest;
use App\Services\ProofVerificationService;
use App\Services\VerifierProofService;
use Illuminate\Http\JsonResponse;

class VerifierVerificationController extends Controller
{
    public function __construct(
        private VerifierProofService $verifierProofService,
        private ProofVerificationService $proofVerificationService
    ) {
    }

    public function read(VerificationReadRequest $request): JsonResponse
    {
        return response()->json($this->verifierProofService->read($request->validated()));
    }

    public function verify(VerifyProofRequest $request): JsonResponse
    {
        return response()->json($this->proofVerificationService->verify(
            $request->user(),
            (int) $request->validated('proof_id')
        ));
    }
}
