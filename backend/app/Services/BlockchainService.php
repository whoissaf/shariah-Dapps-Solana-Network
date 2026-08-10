<?php

namespace App\Services;

use App\Models\BlockchainLog;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BlockchainService
{
    public function storeProof(User $user, int $proofId): array
    {
        return DB::transaction(function () use ($user, $proofId) {
            $proof = Proof::where('user_id', $user->id)
                ->whereIn('status', ['generated', 'shared'])
                ->find($proofId);

            if (! $proof) {
                throw ValidationException::withMessages([
                    'proof_id' => ['Eligible proof not found for blockchain storage.'],
                ]);
            }

            $existing = BlockchainLog::where('proof_id', $proof->id)
                ->where('status', 'confirmed')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'message' => 'Proof already stored on blockchain.',
                    'stored' => false,
                    'blockchain_log' => $this->format($existing),
                ];
            }

            $nonce = bin2hex(random_bytes(16));

            $txHash = '0x' . hash('sha256', $proof->proof_hash . ':' . $nonce);

            $blockchainLog = BlockchainLog::create([
                'proof_id' => $proof->id,
                'network' => 'ethereum',
                'contract_address' => '0x5fbdb2315678afecb367f032d93f642f64180aa3',
                'tx_hash' => $txHash,
                'block_number' => random_int(100000, 9999999),
                'event_name' => 'ProofStored',
                'payload' => [
                    'proof_hash' => $proof->proof_hash,
                    'nonce' => $nonce,
                    'simulation' => true,
                    'stored_at' => now()->toIso8601String(),
                ],
                'status' => 'confirmed',
            ]);

            return [
                'message' => 'Proof stored on blockchain.',
                'stored' => true,
                'blockchain_log' => $this->format($blockchainLog),
            ];
        });
    }

    private function format(BlockchainLog $blockchainLog): array
    {
        return [
            'id' => $blockchainLog->id,
            'proof_id' => $blockchainLog->proof_id,
            'network' => $blockchainLog->network,
            'contract_address' => $blockchainLog->contract_address,
            'tx_hash' => $blockchainLog->tx_hash,
            'block_number' => $blockchainLog->block_number,
            'event_name' => $blockchainLog->event_name,
            'payload' => $blockchainLog->payload,
            'status' => $blockchainLog->status,
            'created_at' => $blockchainLog->created_at?->toIso8601String(),
        ];
    }
}
