<?php

namespace App\Domain\Workflow\Contracts;

use App\Domain\Workflow\Workflow;
use App\Domain\Workflow\WorkflowRun;
use App\Domain\Workflow\WorkflowStep;

interface WorkflowRepositoryInterface
{
    public function findWorkflowById(string $id): ?Workflow;

    public function findWorkflowBySlug(string $slug): ?Workflow;

    public function findRunById(string $id): ?WorkflowRun;

    /** @return list<WorkflowStep> */
    public function getStepsForRun(string $workflowRunId): array;

    public function getStepById(string $id): ?WorkflowStep;

    public function storeRun(WorkflowRun $run): void;

    public function storeStep(WorkflowStep $step): void;

    public function updateRunStatus(string $runId, string $status, ?string $currentStepId = null, ?\DateTimeInterface $completedAt = null): void;

    public function updateStep(string $stepId, string $status, ?array $inputPayload = null, ?array $outputPayload = null, ?\DateTimeInterface $startedAt = null, ?\DateTimeInterface $completedAt = null, ?int $retryCount = null): void;
}
