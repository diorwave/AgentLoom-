<?php

namespace App\Domain\Workflow;

/**
 * A single step in a workflow run. Tracks status, input/output, and retries.
 */
final class WorkflowStep
{
    public function __construct(
        private string $id,
        private string $workflowRunId,
        private string $stepKey,
        private int $order,
        private StepStatus $status,
        private ?array $inputPayload,
        private ?array $outputPayload,
        private int $retryCount,
        private int $maxRetries,
        private bool $requiresApproval,
        private ?\DateTimeInterface $startedAt,
        private ?\DateTimeInterface $completedAt,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function workflowRunId(): string
    {
        return $this->workflowRunId;
    }

    public function stepKey(): string
    {
        return $this->stepKey;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function status(): StepStatus
    {
        return $this->status;
    }

    public function inputPayload(): ?array
    {
        return $this->inputPayload;
    }

    public function outputPayload(): ?array
    {
        return $this->outputPayload;
    }

    public function retryCount(): int
    {
        return $this->retryCount;
    }

    public function maxRetries(): int
    {
        return $this->maxRetries;
    }

    public function requiresApproval(): bool
    {
        return $this->requiresApproval;
    }

    public function startedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function completedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function canRetry(): bool
    {
        return $this->status === StepStatus::Failed && $this->retryCount < $this->maxRetries;
    }
}
