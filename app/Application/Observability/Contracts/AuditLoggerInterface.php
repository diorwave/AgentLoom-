<?php

namespace App\Application\Observability\Contracts;

interface AuditLoggerInterface
{
    public function logWorkflowEvent(string $workflowRunId, ?string $stepId, string $eventType, array $payload = []): void;

    public function logLLMCall(string $workflowRunId, ?string $stepId, string $provider, string $model, int $inputTokens, int $outputTokens, ?float $latencyMs = null, ?float $estimatedCost = null): void;

    public function logToolExecution(string $workflowRunId, ?string $stepId, string $toolName, array $requestSnapshot, array $resultSnapshot, ?bool $approved = null): void;
}
