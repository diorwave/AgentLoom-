<?php

namespace App\Domain\Approval;

/**
 * A request for human approval of a tool execution.
 */
final class ApprovalRequest
{
    public function __construct(
        private string $id,
        private string $workflowRunId,
        private string $stepId,
        private string $toolName,
        private array $toolArguments,
        private ApprovalStatus $status,
        private \DateTimeInterface $requestedAt,
        private ?\DateTimeInterface $resolvedAt,
        private ?string $resolvedBy,
        private ?string $comment,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function workflowRunId(): string
    {
        return $this->workflowRunId;
    }

    public function stepId(): string
    {
        return $this->stepId;
    }

    public function toolName(): string
    {
        return $this->toolName;
    }

    public function toolArguments(): array
    {
        return $this->toolArguments;
    }

    public function status(): ApprovalStatus
    {
        return $this->status;
    }

    public function requestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function resolvedAt(): ?\DateTimeInterface
    {
        return $this->resolvedAt;
    }

    public function resolvedBy(): ?string
    {
        return $this->resolvedBy;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::Pending;
    }
}
