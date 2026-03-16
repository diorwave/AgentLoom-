<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->string('step_key', 64);
            $table->unsignedInteger('order');
            $table->string('status', 32);
            $table->jsonb('input_payload')->nullable();
            $table->jsonb('output_payload')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->unsignedInteger('max_retries')->default(3);
            $table->boolean('requires_approval')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->index('workflow_run_id');
            $table->index(['workflow_run_id', 'order']);
        });

        Schema::table('workflow_runs', function (Blueprint $table) {
            $table->foreign('current_step_id')->references('id')->on('workflow_steps')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_runs', function (Blueprint $table) {
            $table->dropForeign(['current_step_id']);
        });
        Schema::dropIfExists('workflow_steps');
    }
};
