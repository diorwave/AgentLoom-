<?php

namespace App\Infrastructure\Workflow;

use App\Domain\Workflow\Contracts\WorkflowRepositoryInterface;
use App\Domain\Workflow\Workflow;

/**
 * Reads workflow templates from config (ai-workflow.php). No DB table for workflows in V1.
 */
final class ConfigWorkflowRepository implements WorkflowRepositoryInterface
{
    public function __construct(
        private readonly array $workflowsConfig
    ) {}

    public function findWorkflowById(string $id): ?Workflow
    {
        if (isset($this->workflowsConfig[$id])) {
            return $this->hydrate($id, $this->workflowsConfig[$id]);
        }
        return null;
    }

    public function findWorkflowBySlug(string $slug): ?Workflow
    {
        $config = $this->workflowsConfig[$slug] ?? null;
        if ($config === null) {
            return null;
        }
        return $this->hydrate($slug, $config);
    }

    private function hydrate(string $slug, array $config): Workflow
    {
        $definition = [
            'steps' => $config['steps'] ?? [],
            'approval_step_keys' => $config['approval_step_keys'] ?? [],
        ];
        return new Workflow(
            id: $slug,
            name: $config['name'] ?? $slug,
            slug: $config['slug'] ?? $slug,
            description: $config['description'] ?? null,
            definition: $definition,
        );
    }

    public function findRunById(string $id): ?\App\Domain\Workflow\WorkflowRun
    {
        return null;
    }

    public function getStepsForRun(string $workflowRunId): array
    {
        return [];
    }

    public function getStepById(string $id): ?\App\Domain\Workflow\WorkflowStep
    {
        return null;
    }

    public function storeRun(\App\Domain\Workflow\WorkflowRun $run): void
    {
    }

    public function storeStep(\App\Domain\Workflow\WorkflowStep $step): void
    {
    }

    public function updateRunStatus(string $runId, string $status, ?string $currentStepId = null, ?\DateTimeInterface $completedAt = null): void
    {
    }

    public function updateStep(string $stepId, string $status, ?array $inputPayload = null, ?array $outputPayload = null, ?\DateTimeInterface $startedAt = null, ?\DateTimeInterface $completedAt = null, ?int $retryCount = null): void
    {
    }
}
