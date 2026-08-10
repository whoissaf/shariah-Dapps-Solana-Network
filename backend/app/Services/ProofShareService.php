<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProofShareService
{
    public function share(User $user, int $proofId): array
    {
        return DB::transaction(function () use ($user, $proofId) {
            $proof = Proof::where('user_id', $user->id)
                ->whereIn('status', ['generated', 'shared'])
                ->find($proofId);

            if (! $proof) {
                throw ValidationException::withMessages([
                    'proof_id' => ['Shareable proof not found.'],
                ]);
            }

            $nonce = bin2hex(random_bytes(16));
            $expiresAt = now()->addMinutes(10);

            $signature = hash_hmac('sha256', implode(':', [
                $proof->id,
                $nonce,
                $proof->proof_hash,
                $expiresAt->toIso8601String(),
            ]), (string) config('app.key'));

            $qrContent = json_encode([
                'proof_id' => $proof->id,
                'nonce' => $nonce,
                'signature' => $signature,
                'expires_at' => $expiresAt->toIso8601String(),
            ]);

            $proof->forceFill([
                'qr_nonce' => $nonce,
                'qr_signature' => $signature,
                'qr_expires_at' => $expiresAt,
                'status' => 'shared',
            ])->save();

            return [
                'message' => 'Proof QR generated.',
                'qr' => [
                    'proof_id' => $proof->id,
                    'qr_nonce' => $nonce,
                    'qr_signature' => $signature,
                    'qr_expires_at' => $expiresAt->toIso8601String(),
                    'expires_in_seconds' => $expiresAt->getTimestamp() - now()->getTimestamp(),
                    'qr_content' => $qrContent,
                ],
                'proof' => [
                    'id' => $proof->id,
                    'status' => $proof->status,
                ],
            ];
        });
    }
}
