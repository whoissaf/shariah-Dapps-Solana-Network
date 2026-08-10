<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use App\Models\Wallet;

class ProfileService
{
    public function show(User $user): array
    {
        $wallet = Wallet::where('user_id', $user->id)
            ->orderByDesc('id')
            ->first();

        $identity = Identity::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if (! $identity) {
            $identity = Identity::where('user_id', $user->id)
                ->orderByDesc('id')
                ->first();
        }

        return [
            'message' => 'User profile.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified' => $user->email_verified_at !== null,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'wallet' => $wallet ? $this->formatWallet($wallet) : null,
            'identity' => $identity ? $this->formatIdentity($identity) : null,
            'summary' => [
                'claims_total' => Claim::where('user_id', $user->id)->count(),
                'claims_eligible' => Claim::where('user_id', $user->id)->where('status', 'eligible')->count(),
                'claims_ineligible' => Claim::where('user_id', $user->id)->where('status', 'ineligible')->count(),
                'proofs_total' => Proof::where('user_id', $user->id)->count(),
                'proofs_generated' => Proof::where('user_id', $user->id)->where('status', 'generated')->count(),
                'proofs_shared' => Proof::where('user_id', $user->id)->where('status', 'shared')->count(),
                'proofs_verified' => Proof::where('user_id', $user->id)->where('status', 'verified')->count(),
                'proofs_rejected' => Proof::where('user_id', $user->id)->where('status', 'rejected')->count(),
                'proofs_expired' => Proof::where('user_id', $user->id)->where('status', 'expired')->count(),
            ],
            'meta' => [
                'app_version' => '1.0.0-mvp',
                'api_version' => 'v1',
                'backend' => 'laravel',
            ],
        ];
    }

    private function formatWallet(Wallet $wallet): array
    {
        return [
            'wallet_address' => $wallet->wallet_address,
            'status' => $wallet->status,
            'connected_at' => $wallet->connected_at?->toIso8601String(),
        ];
    }

    private function formatIdentity(Identity $identity): array
    {
        return [
            'anonymous_id' => $identity->anonymous_id,
            'identity_commitment' => $identity->identity_commitment,
            'status' => $identity->status,
            'created_at' => $identity->created_at?->toIso8601String(),
        ];
    }
}
