<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentUploadRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private DocumentService $documentService)
    {
    }

    public function upload(DocumentUploadRequest $request): JsonResponse
    {
        $result = $this->documentService->upload($request->user(), $request->validated());

        return response()->json($result, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->documentService->list($request->user());

        return response()->json($result);
    }
}
