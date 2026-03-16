<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('status', 32);
            $table->jsonb('context')->nullable();
            $table->ulid('current_step_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('workflow_runs', function (Blueprint $table) {
            $table->index('workflow_id');
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_runs');
    }
};
