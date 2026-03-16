<?php

namespace App\Domain\Workflow;

/**
 * Workflow template: definition of steps and approval points.
 * No runtime state; used to create WorkflowRun instances.
 */
final class Workflow
{
    public function __construct(
        private string $id,
        private string $name,
        private string $slug,
        private ?string $description,
        /** @var array{steps: array<int, array{key: string, order: int, agent_role: ?string, max_retries: int}>, approval_step_keys: array<string>} */
        private array $definition,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /** @return array{steps: array, approval_step_keys: array<string>} */
    public function definition(): array
    {
        return $this->definition;
    }

    /** @return list<array{key: string, order: int, agent_role: ?string, max_retries: int}> */
    public function steps(): array
    {
        return $this->definition['steps'] ?? [];
    }

    /** @return array<string> */
    public function approvalStepKeys(): array
    {
        return $this->definition['approval_step_keys'] ?? [];
    }

    public function requiresApprovalForStep(string $stepKey): bool
    {
        return in_array($stepKey, $this->approvalStepKeys(), true);
    }
}
