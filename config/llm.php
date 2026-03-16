<?php

/**
 * LLM provider configuration.
 * API keys and model settings; never expose keys in logs.
 */

return [
    'default' => env('LLM_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'default_model' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'timeout' => (int) env('LLM_TIMEOUT', 60),
            'max_tokens' => (int) env('LLM_MAX_TOKENS', 4096),
        ],
    ],

    'logging' => [
        'log_requests' => env('LLM_LOG_REQUESTS', true),
        'log_responses' => env('LLM_LOG_RESPONSES', false), // Full response body; set false in prod if PII
        'truncate_content_length' => (int) env('LLM_TRUNCATE_LOG_LENGTH', 500),
    ],

    'cost_estimation' => [
        'openai' => [
            'gpt-4o' => ['input' => 2.5, 'output' => 10.0],   // per 1M tokens, USD
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.6],
            'gpt-4-turbo' => ['input' => 10.0, 'output' => 30.0],
            'text-embedding-3-small' => ['input' => 0.02, 'output' => 0],
        ],
    ],
];
