<?php

namespace App\Domain\Tool;

/**
 * Value object: a request to execute a tool (from LLM or application).
 */
final class ToolCallRequest
{
    public function __construct(
        private string $toolName,
        private array $arguments,
        private string $requestedByAgent,
        private string $workflowRunId,
        private string $stepId,
    ) {}

    public function toolName(): string
    {
        return $this->toolName;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function requestedByAgent(): string
    {
        return $this->requestedByAgent;
    }

    public function workflowRunId(): string
    {
        return $this->workflowRunId;
    }

    public function stepId(): string
    {
        return $this->stepId;
    }
}
