<?php

namespace App\Domain\Tool;

/**
 * Definition of a tool: name, argument schema, approval policy.
 */
final class ToolDefinition
{
    public function __construct(
        private string $name,
        private string $description,
        private array $schema,
        private bool $requiresApproval,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    /** @return array JSON Schema for arguments */
    public function schema(): array
    {
        return $this->schema;
    }

    public function requiresApproval(): bool
    {
        return $this->requiresApproval;
    }
}
