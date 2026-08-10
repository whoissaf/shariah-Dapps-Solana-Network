<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerificationDecisionService
{
    public function approve(User $verifier, int $verificationId): array
    {
        return DB::transaction(function () use ($verifier, $verificationId) {
            $verification = $this->getPendingVerification($verificationId);

            return $this->applyDecision(
                $verifier,
                $verification,
                'verified',
                null,
                'Verification approved.'
            );
        });
    }

    public function reject(User $verifier, int $verificationId, string $reason): array
    {
        return DB::transaction(function () use ($verifier, $verificationId, $reason) {
            $verification = $this->getPendingVerification($verificationId);

            return $this->applyDecision(
                $verifier,
                $verification,
                'rejected',
                $reason,
                'Verification rejected.'
            );
        });
    }

    private function getPendingVerification(int $verificationId): Verification
    {
        $verification = Verification::find($verificationId);

        if (! $verification) {
            throw ValidationException::withMessages([
                'verification_id' => ['Verification not found.'],
            ]);
        }

        if ($verification->status !== 'pending') {
            throw ValidationException::withMessages([
                'verification_id' => ['Verification already has final decision.'],
            ]);
        }

        return $verification;
    }

    private function applyDecision(
        User $verifier,
        Verification $verification,
        string $status,
        ?string $reason,
        string $message
    ): array {
        $proof = Proof::find($verification->proof_id);

        if (! $proof) {
            throw ValidationException::withMessages([
                'verification_id' => ['Proof linked to verification not found.'],
            ]);
        }

        $result = $verification->result ?? [];

        $result['decision'] = [
            'status' => $status,
            'verifier_id' => $verifier->id,
            'reason' => $reason,
            'decided_at' => now()->toIso8601String(),
        ];

        $verification->forceFill([
            'status' => $status,
            'result' => $result,
            'reject_reason' => $reason,
            'verified_at' => now(),
        ])->save();

        $proof->forceFill([
            'status' => $status,
        ])->save();

        return [
            'message' => $message,
            'verification' => [
                'id' => $verification->id,
                'proof_id' => $verification->proof_id,
                'status' => $verification->status,
                'reject_reason' => $verification->reject_reason,
                'verified_at' => $verification->verified_at?->toIso8601String(),
                'decision' => $result['decision'],
            ],
            'proof' => [
                'id' => $proof->id,
                'status' => $proof->status,
            ],
        ];
    }
}
