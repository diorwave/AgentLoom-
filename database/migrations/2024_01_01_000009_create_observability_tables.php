<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_run_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignUlid('step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->string('event_type', 64);
            $table->jsonb('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('workflow_run_logs', function (Blueprint $table) {
            $table->index(['workflow_run_id', 'created_at']);
        });

        Schema::create('llm_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignUlid('step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->string('provider', 64);
            $table->string('model', 128);
            $table->unsignedInteger('input_tokens');
            $table->unsignedInteger('output_tokens');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->decimal('estimated_cost', 12, 6)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('llm_call_logs', function (Blueprint $table) {
            $table->index(['workflow_run_id', 'created_at']);
        });

        Schema::create('tool_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignUlid('step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->string('tool_name', 128);
            $table->jsonb('request_snapshot')->nullable();
            $table->jsonb('result_snapshot')->nullable();
            $table->boolean('approved')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('tool_execution_logs', function (Blueprint $table) {
            $table->index(['workflow_run_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_execution_logs');
        Schema::dropIfExists('llm_call_logs');
        Schema::dropIfExists('workflow_run_logs');
    }
};
