<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;

class WalletService
{
    public function connect(User $user, array $data): array
    {
        $session = bin2hex(random_bytes(32));

        $wallet = Wallet::firstOrNew([
            'user_id' => $user->id,
        ]);

        $wallet->fill([
            'wallet_address' => $data['wallet_address'],
            'wallet_session' => $session,
            'connected_at' => now(),
            'status' => 'connected',
        ])->save();

        return [
            'message' => 'Wallet connected.',
            'wallet' => [
                'id' => $wallet->id,
                'wallet_address' => $wallet->wallet_address,
                'status' => $wallet->status,
                'connected_at' => $wallet->connected_at?->toIso8601String(),
                'wallet_session' => $wallet->wallet_session,
            ],
        ];
    }

    public function profile(User $user): array
    {
        $wallet = Wallet::where('user_id', $user->id)->first();

        return [
            'message' => 'Wallet profile.',
            'wallet' => $wallet ? [
                'id' => $wallet->id,
                'wallet_address' => $wallet->wallet_address,
                'status' => $wallet->status,
                'connected_at' => $wallet->connected_at?->toIso8601String(),
            ] : null,
        ];
    }
}
