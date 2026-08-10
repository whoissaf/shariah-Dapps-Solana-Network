<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'user_id',
        'identity_id',
        'claim_type',
        'status',
        'payload',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
}
