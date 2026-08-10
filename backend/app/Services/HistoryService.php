<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;

class HistoryService
{
    public function list(User $user): array
    {
        $proofs = Proof::with(['claim', 'latestVerification'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return [
            'message' => 'Verification history.',
            'history' => $proofs->map(function (Proof $proof) {
                return $this->format($proof);
            })->all(),
        ];
    }

    private function format(Proof $proof): array
    {
        return [
            'proof_id' => $proof->id,
            'claim_id' => $proof->claim_id,
            'claim_type' => $proof->claim?->claim_type,
            'proof_status' => $proof->status,
            'verification_status' => $proof->latestVerification?->status,
            'display_status' => $this->displayStatus($proof),
            'qr_expires_at' => $proof->qr_expires_at?->toIso8601String(),
            'created_at' => $proof->created_at?->toIso8601String(),
            'updated_at' => $proof->updated_at?->toIso8601String(),
        ];
    }

    private function displayStatus(Proof $proof): string
    {
        if ($proof->status === 'expired') {
            return 'expired';
        }

        if ($proof->latestVerification && in_array($proof->latestVerification->status, ['verified', 'rejected'])) {
            return $proof->latestVerification->status;
        }

        return $proof->status;
    }
}
