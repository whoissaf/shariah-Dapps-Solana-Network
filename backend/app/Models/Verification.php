<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    protected $fillable = [
        'proof_id',
        'verifier_id',
        'status',
        'result',
        'ai_explanation',
        'reject_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'ai_explanation' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function proof(): BelongsTo
    {
        return $this->belongsTo(Proof::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
