<?php

namespace App\Services;

use App\Models\Verification;

class VerifierDashboardService
{
    public function summary(): array
    {
        return [
            'message' => 'Verifier dashboard.',
            'dashboard' => [
                'pending' => Verification::where('status', 'pending')->count(),
                'verified' => Verification::where('status', 'verified')->count(),
                'rejected' => Verification::where('status', 'rejected')->count(),
                'today_total' => Verification::whereDate('created_at', now()->toDateString())->count(),
            ],
            'recent' => [],
        ];
    }
}
