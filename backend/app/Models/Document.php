<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'claim_id',
        'document_type',
        'original_name',
        'file_path',
        'mime_type',
        'size_bytes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
