<?php

namespace App\Http\Controllers;

use App\Services\IdentityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdentityController extends Controller
{
    public function __construct(private IdentityService $identityService)
    {
    }

    public function create(Request $request): JsonResponse
    {
        $result = $this->identityService->create($request->user());

        $status = $result['created'] ? 201 : 200;

        return response()->json($result, $status);
    }

    public function profile(Request $request): JsonResponse
    {
        $result = $this->identityService->current($request->user());

        return response()->json($result);
    }
}
