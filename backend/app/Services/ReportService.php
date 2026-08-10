<?php

namespace App\Services;

use App\Models\Verification;

class ReportService
{
    public function exportCsv(array $filters): array
    {
        $verifications = $this->query($filters)->get();

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'verification_id',
            'proof_id',
            'claim_type',
            'verification_status',
            'reject_reason',
            'verifier_name',
            'verifier_email',
            'verified_at',
            'created_at',
            'blockchain_tx_hash',
            'blockchain_network',
            'blockchain_block_number',
            'blockchain_status',
            'ai_recommendation',
        ]);

        foreach ($verifications as $verification) {
            fputcsv($handle, $this->row($verification));
        }

        rewind($handle);

        $csv = stream_get_contents($handle);

        fclose($handle);

        return [
            'verification-report.csv',
            $csv,
        ];
    }

    private function query(array $filters)
    {
        $query = Verification::with([
            'proof.claim',
            'proof.latestBlockchainLog',
            'verifier',
        ])->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function row(Verification $verification): array
    {
        $proof = $verification->proof;
        $blockchainLog = $proof?->latestBlockchainLog;

        return [
            $verification->id,
            $verification->proof_id,
            $proof?->claim?->claim_type,
            $verification->status,
            $verification->reject_reason,
            $verification->verifier?->name,
            $verification->verifier?->email,
            $verification->verified_at?->toIso8601String(),
            $verification->created_at?->toIso8601String(),
            $blockchainLog?->tx_hash,
            $blockchainLog?->network,
            $blockchainLog?->block_number,
            $blockchainLog?->status,
            $verification->ai_explanation['recommendation'] ?? null,
        ];
    }
}
