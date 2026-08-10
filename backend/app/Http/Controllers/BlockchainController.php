<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockchainStoreRequest;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;

class BlockchainController extends Controller
{
    public function __construct(private BlockchainService $blockchainService)
    {
    }

    public function store(BlockchainStoreRequest $request): JsonResponse
    {
        $result = $this->blockchainService->storeProof(
            $request->user(),
            (int) $request->validated('proof_id')
        );

        $status = $result['stored'] ? 201 : 200;

        return response()->json($result, $status);
    }
}
