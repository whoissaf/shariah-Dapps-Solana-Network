<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClaimCreateRequest;
use App\Services\ClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function __construct(private ClaimService $claimService)
    {
    }

    public function create(ClaimCreateRequest $request): JsonResponse
    {
        $result = $this->claimService->create($request->user(), $request->validated());

        return response()->json($result, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->claimService->list($request->user());

        return response()->json($result);
    }
}
