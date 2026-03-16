<?php

/**
 * AI Workflow platform configuration.
 * Workflow definitions, agent profiles, and step configuration.
 */

return [
    'default_workflow' => env('AI_WORKFLOW_DEFAULT', 'document_analysis'),

    'workflows' => [
        'document_analysis' => [
            'name' => 'Document Analysis',
            'slug' => 'document_analysis',
            'description' => 'Upload documents → plan → extract evidence → review → summarize.',
            'steps' => [
                ['key' => 'ingest', 'order' => 1, 'agent_role' => null, 'max_retries' => 2],
                ['key' => 'plan', 'order' => 2, 'agent_role' => 'planner', 'max_retries' => 2],
                ['key' => 'analyse', 'order' => 3, 'agent_role' => 'analyst', 'max_retries' => 2],
                ['key' => 'review', 'order' => 4, 'agent_role' => 'reviewer', 'max_retries' => 2],
                ['key' => 'summarize', 'order' => 5, 'agent_role' => 'summarizer', 'max_retries' => 2],
            ],
            'approval_step_keys' => [], // Optional: ['analyse'] if analyst step required approval
        ],
    ],

    'agents' => [
        'planner' => [
            'name' => 'Planner',
            'system_prompt' => 'You are a planning agent. Given document metadata and an optional user question, produce a structured analysis plan: sections to analyse, questions to answer, and order of operations. Output valid JSON with keys: sections, questions, priority.',
            'allowed_tools' => [],
            'default_model' => null,
        ],
        'analyst' => [
            'name' => 'Analyst',
            'system_prompt' => 'You are an analyst agent. Use the provided document chunks and analysis plan to extract evidence and structured findings. For each finding, cite the source chunk IDs. Output structured data with findings and evidence_references (chunk_id, document_id, excerpt).',
            'allowed_tools' => ['retrieve_chunks'],
            'default_model' => null,
        ],
        'reviewer' => [
            'name' => 'Reviewer',
            'system_prompt' => 'You are a reviewer agent. Validate the analyst findings for quality, consistency, and sufficiency. Output: validation_result (approved|amendments), comments, and if amendments, a list of required changes.',
            'allowed_tools' => [],
            'default_model' => null,
        ],
        'summarizer' => [
            'name' => 'Summarizer',
            'system_prompt' => 'You are a summarizer agent. Produce a final report from the approved findings and review. Output clear, structured text suitable for the end user.',
            'allowed_tools' => [],
            'default_model' => null,
        ],
    ],
];
