<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    public function __construct(private AuditService $auditService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->auditService->list());
    }
}
