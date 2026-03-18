<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Document extends Model
{
    protected $table = 'documents';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'original_name',
        'mime_type',
        'storage_path',
        'status',
        'meta',
        'user_id',
        'workflow_run_id',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->id = $model->id ?: Str::ulid()->toRfc4122();
        });
    }
}

