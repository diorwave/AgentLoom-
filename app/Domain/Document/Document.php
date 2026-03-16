<?php

namespace App\Domain\Document;

/**
 * Uploaded document entity. Untrusted input; status tracks ingestion pipeline.
 */
final class Document
{
    public function __construct(
        private string $id,
        private string $name,
        private string $originalName,
        private string $mimeType,
        private string $storagePath,
        private DocumentStatus $status,
        private ?array $meta,
        private ?string $userId,
        private ?string $workflowRunId,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function storagePath(): string
    {
        return $this->storagePath;
    }

    public function status(): DocumentStatus
    {
        return $this->status;
    }

    public function meta(): ?array
    {
        return $this->meta;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function workflowRunId(): ?string
    {
        return $this->workflowRunId;
    }
}
