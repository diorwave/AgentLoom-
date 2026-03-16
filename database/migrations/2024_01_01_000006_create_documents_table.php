<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('original_name');
            $table->string('mime_type', 128);
            $table->string('storage_path');
            $table->string('status', 32);
            $table->jsonb('meta')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('workflow_run_id')->nullable()->constrained('workflow_runs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->index('status');
            $table->index('workflow_run_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
