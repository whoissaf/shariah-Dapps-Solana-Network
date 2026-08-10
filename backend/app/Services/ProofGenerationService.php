<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProofGenerationService
{
    public function generate(User $user, int $claimId): array
    {
        return DB::transaction(function () use ($user, $claimId) {
            $claim = Claim::where('user_id', $user->id)
                ->where('status', 'eligible')
                ->find($claimId);

            if (! $claim) {
                throw ValidationException::withMessages([
                    'claim_id' => ['Eligible claim not found.'],
                ]);
            }

            $identity = Identity::where('id', $claim->identity_id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (! $identity) {
                throw ValidationException::withMessages([
                    'identity' => ['Active identity is required to generate proof.'],
                ]);
            }

            $nonce = bin2hex(random_bytes(16));

            $proofHash = hash('sha256', json_encode([
                'claim_id' => $claim->id,
                'identity_commitment' => $identity->identity_commitment,
                'claim_type' => $claim->claim_type,
                'payload' => $claim->payload,
                'nonce' => $nonce,
                'generated_at' => now()->toIso8601String(),
            ]));

            $proof = Proof::create([
                'user_id' => $user->id,
                'claim_id' => $claim->id,
                'identity_id' => $identity->id,
                'proof_hash' => $proofHash,
                'proof_payload' => [
                    'circuit' => 'mvp-zk-simulation',
                    'claim_type' => $claim->claim_type,
                    'identity_commitment' => $identity->identity_commitment,
                    'nonce' => $nonce,
                    'public_inputs' => [
                        'claim_id' => $claim->id,
                        'rule_version' => 'default-v1',
                    ],
                ],
                'status' => 'generated',
            ]);

            $claim->forceFill([
                'status' => 'proof_generated',
            ])->save();

            return [
                'message' => 'Proof generated.',
                'proof' => $this->format($proof),
            ];
        });
    }

    private function format(Proof $proof): array
    {
        return [
            'id' => $proof->id,
            'claim_id' => $proof->claim_id,
            'identity_id' => $proof->identity_id,
            'proof_hash' => $proof->proof_hash,
            'proof_payload' => $proof->proof_payload,
            'status' => $proof->status,
            'created_at' => $proof->created_at?->toIso8601String(),
        ];
    }
}
