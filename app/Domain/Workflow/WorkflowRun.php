<?php

namespace App\Domain\Workflow;

/**
 * A single execution instance of a workflow. Aggregate root for run and steps.
 */
final class WorkflowRun
{
    public function __construct(
        private string $id,
        private string $workflowId,
        private WorkflowStatus $status,
        private array $context,
        private ?string $currentStepId,
        private ?\DateTimeInterface $startedAt,
        private ?\DateTimeInterface $completedAt,
        private ?string $userId,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function workflowId(): string
    {
        return $this->workflowId;
    }

    public function status(): WorkflowStatus
    {
        return $this->status;
    }

    public function context(): array
    {
        return $this->context;
    }

    public function currentStepId(): ?string
    {
        return $this->currentStepId;
    }

    public function startedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function completedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function isResumable(): bool
    {
        return $this->status === WorkflowStatus::Running
            || $this->status === WorkflowStatus::AwaitingApproval
            || $this->status === WorkflowStatus::RetryScheduled;
    }
}
