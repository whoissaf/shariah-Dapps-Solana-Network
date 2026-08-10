<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleValidateRequest;
use App\Services\RuleValidationService;
use Illuminate\Http\JsonResponse;

class RuleController extends Controller
{
    public function __construct(private RuleValidationService $ruleValidationService)
    {
    }

    public function validateClaim(RuleValidateRequest $request): JsonResponse
    {
        $result = $this->ruleValidationService->validateClaim(
            $request->user(),
            (int) $request->validated('claim_id')
        );

        return response()->json($result);
    }
}
