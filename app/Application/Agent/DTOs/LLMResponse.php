<?php

namespace App\Application\Agent\DTOs;

/**
 * DTO for LLM chat response. Content and optional tool calls.
 */
final class LLMResponse
{
    public function __construct(
        public readonly string $content,
        /** @var list<array{name: string, arguments: array}> */
        public readonly array $toolCalls,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly ?string $model = null,
        public readonly ?float $latencyMs = null,
    ) {}
}
