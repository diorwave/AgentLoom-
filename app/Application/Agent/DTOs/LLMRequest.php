<?php

namespace App\Application\Agent\DTOs;

/**
 * DTO for LLM chat request. System and user/context messages kept separate.
 */
final class LLMRequest
{
    public function __construct(
        public readonly string $systemPrompt,
        /** @var list<array{role: string, content: string}> */
        public readonly array $messages,
        /** @var list<array{name: string, description: string, parameters: array}>|null */
        public readonly ?array $tools = null,
        public readonly ?string $model = null,
        public readonly int $maxTokens = 4096,
    ) {}
}
