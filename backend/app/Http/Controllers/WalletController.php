<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletConnectRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService)
    {
    }

    public function connect(WalletConnectRequest $request): JsonResponse
    {
        $result = $this->walletService->connect($request->user(), $request->validated());

        return response()->json($result);
    }

    public function profile(Request $request): JsonResponse
    {
        $result = $this->walletService->profile($request->user());

        return response()->json($result);
    }
}
