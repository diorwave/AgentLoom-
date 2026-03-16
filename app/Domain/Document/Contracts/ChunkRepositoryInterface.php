<?php

namespace App\Domain\Document\Contracts;

use App\Domain\Document\DocumentChunk;

interface ChunkRepositoryInterface
{
    public function store(DocumentChunk $chunk, ?array $embedding = null): void;

    /**
     * Semantic search: return chunks most similar to the query embedding.
     *
     * @param  array<float>  $queryEmbedding
     * @param  array<string>  $documentIds  optional filter
     * @return list<array{chunk: DocumentChunk, score: float}>
     */
    public function searchByEmbedding(array $queryEmbedding, int $topK = 10, array $documentIds = []): array;

    /** @return list<DocumentChunk> */
    public function getByDocumentId(string $documentId): array;
}
