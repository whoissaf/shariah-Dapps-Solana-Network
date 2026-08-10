<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\Proof;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VerifierProofService
{
    public function read(array $data): array
    {
        $proof = Proof::with('claim')->find($data['proof_id']);

        if (! $proof) {
            throw ValidationException::withMessages([
                'qr' => ['Invalid QR.'],
            ]);
        }

        if (! $proof->qr_nonce || ! $proof->qr_signature || ! $proof->qr_expires_at) {
            throw ValidationException::withMessages([
                'qr' => ['QR is not available for this proof.'],
            ]);
        }

        if (! hash_equals($proof->qr_nonce, $data['nonce'])) {
            throw ValidationException::withMessages([
                'qr' => ['Invalid QR.'],
            ]);
        }

        $providedExpiresAt = Carbon::parse($data['expires_at']);

        if (! $proof->qr_expires_at->equalTo($providedExpiresAt)) {
            throw ValidationException::withMessages([
                'qr' => ['Invalid QR.'],
            ]);
        }

        $expectedSignature = hash_hmac('sha256', implode(':', [
            $proof->id,
            $proof->qr_nonce,
            $proof->proof_hash,
            $proof->qr_expires_at->toIso8601String(),
        ]), (string) config('app.key'));

        if (! hash_equals($expectedSignature, $data['signature'])) {
            throw ValidationException::withMessages([
                'qr' => ['Invalid QR signature.'],
            ]);
        }

        if ($proof->qr_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'qr' => ['QR expired.'],
            ]);
        }

        return [
            'message' => 'QR valid.',
            'proof' => $this->formatProof($proof),
        ];
    }

    public function show(int $proofId): array
    {
        $proof = Proof::with('claim')->find($proofId);

        if (! $proof) {
            throw ValidationException::withMessages([
                'proof_id' => ['Proof not found.'],
            ]);
        }

        if (! in_array($proof->status, ['shared', 'verified', 'rejected', 'expired'])) {
            throw ValidationException::withMessages([
                'proof_id' => ['Proof is not available for verification.'],
            ]);
        }

        return [
            'message' => 'Proof fetched.',
            'proof' => $this->formatProof($proof),
        ];
    }

    private function formatProof(Proof $proof): array
    {
        $identity = Identity::find($proof->identity_id);

        $wallet = Wallet::where('user_id', $proof->user_id)
            ->orderByDesc('id')
            ->first();

        return [
            'proof_id' => $proof->id,
            'status' => $proof->status,
            'proof_hash' => $proof->proof_hash,
            'created_at' => $proof->created_at?->toIso8601String(),
            'qr_expires_at' => $proof->qr_expires_at?->toIso8601String(),
            'claim' => $proof->claim ? [
                'claim_id' => $proof->claim->id,
                'claim_type' => $proof->claim->claim_type,
                'claim_status' => $proof->claim->status,
                'payload' => $proof->claim->payload,
                'submitted_at' => $proof->claim->submitted_at?->toIso8601String(),
            ] : null,
            'identity' => $identity ? [
                'anonymous_id' => $identity->anonymous_id,
                'identity_commitment' => $identity->identity_commitment,
                'identity_status' => $identity->status,
            ] : null,
            'wallet' => $wallet ? [
                'wallet_address' => $wallet->wallet_address,
                'wallet_status' => $wallet->status,
            ] : null,
        ];
    }
}
