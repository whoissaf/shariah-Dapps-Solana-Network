<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainLog extends Model
{
    protected $fillable = [
        'proof_id',
        'network',
        'contract_address',
        'tx_hash',
        'block_number',
        'event_name',
        'payload',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'block_number' => 'integer',
        ];
    }

    public function proof(): BelongsTo
    {
        return $this->belongsTo(Proof::class);
    }
}
