<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WorkflowRun extends Model
{
    protected $table = 'workflow_runs';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workflow_id',
        'status',
        'context',
        'current_step_id',
        'started_at',
        'completed_at',
        'user_id',
    ];

    protected $casts = [
        'context' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->id = $model->id ?: Str::ulid()->toRfc4122();
        });
    }

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_run_id', 'id');
    }
}

