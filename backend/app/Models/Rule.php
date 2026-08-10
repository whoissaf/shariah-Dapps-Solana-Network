<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'rule_type',
        'parameters',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }
}
