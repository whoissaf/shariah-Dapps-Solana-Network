<?php

namespace App\Http\Controllers;

use App\Services\HistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct(private HistoryService $historyService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->historyService->list($request->user()));
    }
}
