<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_address',
        'wallet_session',
        'connected_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'connected_at' => 'datetime',
        ];
    }
}
