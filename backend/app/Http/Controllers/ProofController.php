<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProofGenerateRequest;
use App\Http\Requests\ProofShareRequest;
use App\Services\ProofGenerationService;
use App\Services\ProofShareService;
use Illuminate\Http\JsonResponse;

class ProofController extends Controller
{
    public function __construct(
        private ProofGenerationService $proofGenerationService,
        private ProofShareService $proofShareService
    ) {
    }

    public function generate(ProofGenerateRequest $request): JsonResponse
    {
        $result = $this->proofGenerationService->generate(
            $request->user(),
            (int) $request->validated('claim_id')
        );

        return response()->json($result, 201);
    }

    public function share(ProofShareRequest $request): JsonResponse
    {
        $result = $this->proofShareService->share(
            $request->user(),
            (int) $request->validated('proof_id')
        );

        return response()->json($result);
    }
}
