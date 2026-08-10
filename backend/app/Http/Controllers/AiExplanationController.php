<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiExplainRequest;
use App\Services\AiExplanationService;
use Illuminate\Http\JsonResponse;

class AiExplanationController extends Controller
{
    public function __construct(private AiExplanationService $aiExplanationService)
    {
    }

    public function explain(AiExplainRequest $request): JsonResponse
    {
        return response()->json($this->aiExplanationService->explain(
            $request->user(),
            (int) $request->validated('verification_id')
        ));
    }
}
