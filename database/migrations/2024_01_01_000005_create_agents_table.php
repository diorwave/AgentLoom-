<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('role', 64);
            $table->string('name');
            $table->text('system_prompt');
            $table->jsonb('allowed_tool_names')->default('[]');
            $table->string('default_model')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
