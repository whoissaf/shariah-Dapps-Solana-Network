<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveVerificationRequest;
use App\Http\Requests\RejectVerificationRequest;
use App\Services\VerificationDecisionService;
use Illuminate\Http\JsonResponse;

class VerifierVerificationDecisionController extends Controller
{
    public function __construct(private VerificationDecisionService $verificationDecisionService)
    {
    }

    public function approve(ApproveVerificationRequest $request): JsonResponse
    {
        return response()->json($this->verificationDecisionService->approve(
            $request->user(),
            (int) $request->validated('verification_id')
        ));
    }

    public function reject(RejectVerificationRequest $request): JsonResponse
    {
        return response()->json($this->verificationDecisionService->reject(
            $request->user(),
            (int) $request->validated('verification_id'),
            (string) $request->validated('reason')
        ));
    }
}
