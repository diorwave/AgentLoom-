<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkflowStep extends Model
{
    protected $table = 'workflow_steps';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workflow_run_id',
        'step_key',
        'order',
        'status',
        'input_payload',
        'output_payload',
        'retry_count',
        'max_retries',
        'requires_approval',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'output_payload' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'requires_approval' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->id = $model->id ?: Str::ulid()->toRfc4122();
        });
    }
}

