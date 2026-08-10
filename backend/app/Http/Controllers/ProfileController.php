<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->profileService->show($request->user()));
    }
}
