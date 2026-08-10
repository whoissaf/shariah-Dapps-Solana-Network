<?php

namespace App\Services;

use App\Models\Identity;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IdentityService
{
    public function create(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $wallet = Wallet::where('user_id', $user->id)
                ->orderByDesc('id')
                ->first();

            $existing = Identity::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existing) {
                if (! $existing->wallet_id && $wallet) {
                    $existing->wallet_id = $wallet->id;
                    $existing->save();
                }

                return [
                    'message' => 'Identity already exists.',
                    'created' => false,
                    'identity' => $this->format($existing),
                ];
            }

            $anonymousId = (string) Str::uuid();
            $secret = bin2hex(random_bytes(32));
            $commitment = hash('sha256', $anonymousId . ':' . $secret);

            $identity = Identity::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet?->id,
                'anonymous_id' => $anonymousId,
                'identity_secret' => $secret,
                'identity_commitment' => $commitment,
                'status' => 'active',
            ]);

            return [
                'message' => 'Identity created.',
                'created' => true,
                'identity' => $this->format($identity),
            ];
        });
    }

    public function current(User $user): array
    {
        $identity = Identity::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return [
            'message' => 'Identity profile.',
            'identity' => $identity ? $this->format($identity) : null,
        ];
    }

    private function format(Identity $identity): array
    {
        return [
            'id' => $identity->id,
            'anonymous_id' => $identity->anonymous_id,
            'wallet_id' => $identity->wallet_id,
            'identity_commitment' => $identity->identity_commitment,
            'status' => $identity->status,
            'created_at' => $identity->created_at?->toIso8601String(),
        ];
    }
}
