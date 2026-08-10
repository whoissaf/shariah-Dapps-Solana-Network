<?php

use App\Http\Controllers\AiExplanationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockchainController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\VerifierDashboardController;
use App\Http\Controllers\VerifierProofController;
use App\Http\Controllers\VerifierVerificationController;
use App\Http\Controllers\VerifierVerificationDecisionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'hackathon-backend',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::prefix('wallet')->group(function () {
        Route::post('/connect', [WalletController::class, 'connect']);
        Route::get('/profile', [WalletController::class, 'profile']);
    });

    Route::prefix('identity')->group(function () {
        Route::post('/create', [IdentityController::class, 'create']);
        Route::get('/', [IdentityController::class, 'profile']);
    });

    Route::prefix('claims')->group(function () {
        Route::post('/create', [ClaimController::class, 'create']);
        Route::get('/', [ClaimController::class, 'index']);
    });

    Route::prefix('documents')->group(function () {
        Route::post('/upload', [DocumentController::class, 'upload']);
        Route::get('/', [DocumentController::class, 'index']);
    });

    Route::prefix('rules')->group(function () {
        Route::post('/validate', [RuleController::class, 'validateClaim']);
    });

    Route::prefix('proof')->group(function () {
        Route::post('/generate', [ProofController::class, 'generate']);
        Route::post('/share', [ProofController::class, 'share']);
    });

    Route::prefix('blockchain')->group(function () {
        Route::post('/store', [BlockchainController::class, 'store']);
    });

    Route::prefix('history')->group(function () {
        Route::get('/', [HistoryController::class, 'index']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
    });
});

Route::middleware(['auth:sanctum', 'verifier'])->group(function () {
    Route::get('/dashboard', [VerifierDashboardController::class, 'index']);

    Route::prefix('verification')->group(function () {
        Route::post('/read', [VerifierVerificationController::class, 'read']);
        Route::post('/verify', [VerifierVerificationController::class, 'verify']);
        Route::post('/approve', [VerifierVerificationDecisionController::class, 'approve']);
        Route::post('/reject', [VerifierVerificationDecisionController::class, 'reject']);
    });

    Route::prefix('ai')->group(function () {
        Route::post('/explain', [AiExplanationController::class, 'explain']);
    });

    Route::get('/proof/{proofId}', [VerifierProofController::class, 'show'])->whereNumber('proofId');

    Route::get('/audit', [AuditController::class, 'index']);

    Route::prefix('report')->group(function () {
        Route::get('/export', [ReportController::class, 'export']);
    });
});
