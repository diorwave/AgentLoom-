<?php

/**
 * Tool definitions for the Tool Execution Gateway.
 * Only tools listed here can be executed; schema and approval policy are enforced.
 */

return [
    'definitions' => [
        'retrieve_chunks' => [
            'name' => 'retrieve_chunks',
            'description' => 'Retrieve relevant document chunks by semantic similarity to a query.',
            'requires_approval' => false,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search query'],
                    'top_k' => ['type' => 'integer', 'description' => 'Max number of chunks', 'default' => 10],
                    'document_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Optional filter by document IDs',
                    ],
                ],
                'required' => ['query'],
            ],
        ],
    ],

    'approval_required_tools' => [
        // Add tool names that always require human approval, e.g. 'send_email', 'export_data'
    ],
];
