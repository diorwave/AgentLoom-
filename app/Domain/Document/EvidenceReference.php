<?php

namespace App\Domain\Document;

/**
 * Value object: reference to a chunk used as evidence in an output.
 */
final class EvidenceReference
{
    public function __construct(
        private string $chunkId,
        private string $documentId,
        private ?string $documentName,
        private ?string $excerpt,
        private ?float $score,
    ) {}

    public function chunkId(): string
    {
        return $this->chunkId;
    }

    public function documentId(): string
    {
        return $this->documentId;
    }

    public function documentName(): ?string
    {
        return $this->documentName;
    }

    public function excerpt(): ?string
    {
        return $this->excerpt;
    }

    public function score(): ?float
    {
        return $this->score;
    }

    /** @return array{chunk_id: string, document_id: string, document_name: ?string, excerpt: ?string, score: ?float} */
    public function toArray(): array
    {
        return [
            'chunk_id' => $this->chunkId,
            'document_id' => $this->documentId,
            'document_name' => $this->documentName,
            'excerpt' => $this->excerpt,
            'score' => $this->score,
        ];
    }
}
