<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'anonymous_id',
        'identity_secret',
        'identity_commitment',
        'status',
    ];

    protected $hidden = [
        'identity_secret',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
