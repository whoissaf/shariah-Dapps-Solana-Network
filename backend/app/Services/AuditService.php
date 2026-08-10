<?php

namespace App\Services;

use App\Models\Verification;

class AuditService
{
    public function list(): array
    {
        $verifications = Verification::with([
            'proof.claim',
            'proof.latestBlockchainLog',
            'verifier',
        ])->orderByDesc('id')->get();

        return [
            'message' => 'Audit trail.',
            'audit' => $verifications->map(function (Verification $verification) {
                return $this->format($verification);
            })->all(),
        ];
    }

    private function format(Verification $verification): array
    {
        $proof = $verification->proof;
        $blockchainLog = $proof?->latestBlockchainLog;

        return [
            'verification_id' => $verification->id,
            'proof_id' => $verification->proof_id,
            'claim_type' => $proof?->claim?->claim_type,
            'verification_status' => $verification->status,
            'reject_reason' => $verification->reject_reason,
            'verifier' => $verification->verifier ? [
                'id' => $verification->verifier->id,
                'name' => $verification->verifier->name,
                'email' => $verification->verifier->email,
            ] : null,
            'timestamp' => $verification->verified_at?->toIso8601String() ?? $verification->updated_at?->toIso8601String(),
            'ethereum_tx' => $blockchainLog ? [
                'tx_hash' => $blockchainLog->tx_hash,
                'network' => $blockchainLog->network,
                'block_number' => $blockchainLog->block_number,
                'contract_address' => $blockchainLog->contract_address,
                'status' => $blockchainLog->status,
                'stored_at' => $blockchainLog->created_at?->toIso8601String(),
            ] : null,
            'created_at' => $verification->created_at?->toIso8601String(),
            'updated_at' => $verification->updated_at?->toIso8601String(),
        ];
    }
}
