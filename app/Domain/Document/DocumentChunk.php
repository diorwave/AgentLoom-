<?php

namespace App\Domain\Document;

/**
 * A chunk of document content with optional embedding reference.
 * Embedding stored in infrastructure (pgvector).
 */
final class DocumentChunk
{
    public function __construct(
        private string $id,
        private string $documentId,
        private string $content,
        private int $position,
        private ?int $tokenCount,
        private ?array $meta,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function documentId(): string
    {
        return $this->documentId;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function tokenCount(): ?int
    {
        return $this->tokenCount;
    }

    public function meta(): ?array
    {
        return $this->meta;
    }
}
