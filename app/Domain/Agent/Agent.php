<?php

namespace App\Domain\Agent;

/**
 * Role-based agent profile: system prompt, allowed tools, optional model.
 */
final class Agent
{
    public function __construct(
        private string $id,
        private AgentRole $role,
        private string $name,
        private string $systemPrompt,
        /** @var list<string> */
        private array $allowedToolNames,
        private ?string $defaultModel,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function role(): AgentRole
    {
        return $this->role;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function systemPrompt(): string
    {
        return $this->systemPrompt;
    }

    /** @return list<string> */
    public function allowedToolNames(): array
    {
        return $this->allowedToolNames;
    }

    public function isToolAllowed(string $toolName): bool
    {
        return in_array($toolName, $this->allowedToolNames, true);
    }

    public function defaultModel(): ?string
    {
        return $this->defaultModel;
    }
}
