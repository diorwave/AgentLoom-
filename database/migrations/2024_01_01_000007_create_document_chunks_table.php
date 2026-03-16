<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedInteger('position');
            $table->unsignedInteger('token_count')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });

        Schema::table('document_chunks', function (Blueprint $table) {
            $table->index('document_id');
        });

        // pgvector: embedding column (1536 dimensions for OpenAI text-embedding-3-small)
        DB::statement('ALTER TABLE document_chunks ADD COLUMN embedding vector(1536)');
    }

    public function down(): void
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });
        Schema::dropIfExists('document_chunks');
    }
};
