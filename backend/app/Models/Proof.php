<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proof extends Model
{
    protected $fillable = [
        'user_id',
        'claim_id',
        'identity_id',
        'proof_hash',
        'proof_payload',
        'qr_signature',
        'qr_nonce',
        'qr_expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'proof_payload' => 'array',
            'qr_expires_at' => 'datetime',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(Verification::class)->latestOfMany();
    }

    public function blockchainLogs(): HasMany
    {
        return $this->hasMany(BlockchainLog::class);
    }

    public function latestBlockchainLog(): HasOne
    {
        return $this->hasOne(BlockchainLog::class)->latestOfMany();
    }
}
