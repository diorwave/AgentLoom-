<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignUlid('step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->string('tool_name', 128);
            $table->jsonb('tool_arguments');
            $table->string('status', 32);
            $table->timestamp('requested_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->index('workflow_run_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
