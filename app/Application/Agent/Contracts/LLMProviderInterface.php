<?php

namespace App\Application\Agent\Contracts;

use App\Application\Agent\DTOs\LLMRequest;
use App\Application\Agent\DTOs\LLMResponse;

interface LLMProviderInterface
{
    public function chat(LLMRequest $request): LLMResponse;

    /**
     * Generate embedding for a single text (or batch).
     *
     * @param  string|array<string>  $input
     * @return array<float> Single embedding when $input is string; array<array<float>> when $input is array of strings
     */
    public function embed(string|array $input): array;
}
