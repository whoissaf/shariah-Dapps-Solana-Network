<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClaimService
{
    public function create(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $identity = Identity::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (! $identity) {
                throw ValidationException::withMessages([
                    'identity' => ['Active identity is required before creating a claim.'],
                ]);
            }

            $claim = Claim::create([
                'user_id' => $user->id,
                'identity_id' => $identity->id,
                'claim_type' => $data['claim_type'],
                'status' => 'submitted',
                'payload' => $data['payload'] ?? [],
                'submitted_at' => now(),
            ]);

            return [
                'message' => 'Claim created.',
                'claim' => $this->format($claim),
            ];
        });
    }

    public function list(User $user): array
    {
        $claims = Claim::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return [
            'message' => 'Claims list.',
            'claims' => $claims->map(function (Claim $claim) {
                return $this->format($claim);
            })->all(),
        ];
    }

    private function format(Claim $claim): array
    {
        return [
            'id' => $claim->id,
            'identity_id' => $claim->identity_id,
            'claim_type' => $claim->claim_type,
            'status' => $claim->status,
            'payload' => $claim->payload,
            'submitted_at' => $claim->submitted_at?->toIso8601String(),
            'created_at' => $claim->created_at?->toIso8601String(),
        ];
    }
}
