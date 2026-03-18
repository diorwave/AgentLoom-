<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentChunk extends Model
{
    protected $table = 'document_chunks';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'document_id',
        'content',
        'position',
        'token_count',
        'meta',
        'embedding',
    ];

    protected $casts = [
        'token_count' => 'integer',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->id = $model->id ?: Str::ulid()->toRfc4122();
        });
    }
}

