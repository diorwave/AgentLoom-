# Proposed Laravel Project Structure

Laravel’s default layout is preserved where it helps (e.g. `app/Http`, `config/`, `routes/`). Domain and application logic are grouped by **module** and **layer** to keep the codebase maintainable as the product grows.

## Repository Root

```
ai-project/
├── app/
│   ├── Domain/                    # Domain layer (framework-agnostic)
│   ├── Application/               # Application / use-case layer
│   ├── Infrastructure/            # Infrastructure implementations
│   ├── Http/                      # Laravel HTTP (controllers, middleware, requests)
│   ├── Providers/
│   ├── Console/
│   └── Exceptions/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── docs/                          # Architecture, API, deployment
├── routes/
├── tests/
├── docker/
├── docker-compose.yml
├── Dockerfile
├── .env.example
├── composer.json
├── phpunit.xml
└── README.md
```

## app/ — Layered Structure

### app/Domain/

Domain entities, value objects, and interfaces. No Laravel or infrastructure imports.

```
app/Domain/
├── Workflow/
│   ├── Workflow.php               # Aggregate: workflow definition (id, name, steps config)
│   ├── WorkflowRun.php            # Aggregate root: single run, status, steps
│   ├── WorkflowStep.php           # Entity: one step in a run (name, status, payloads)
│   ├── WorkflowStatus.php         # Value object / enum
│   ├── StepStatus.php
│   └── Events/                    # Domain events (e.g. WorkflowRunStarted)
├── Agent/
│   ├── Agent.php                  # Role-based agent (id, name, systemPrompt, allowedTools)
│   ├── AgentRole.php              # Enum: planner, analyst, reviewer, summarizer
│   └── Contracts/
│       └── AgentProfileRepositoryInterface.php
├── Tool/
│   ├── ToolDefinition.php         # Name, schema, requiresApproval
│   ├── ToolCallRequest.php        # Value object: tool name + arguments
│   ├── ToolCallResult.php         # Value object: success, output, error
│   └── Contracts/
│       └── ToolExecutionGatewayInterface.php
├── Document/
│   ├── Document.php               # Entity: uploaded document (id, name, mime, status)
│   ├── DocumentChunk.php          # Entity: chunk with content, embedding ref, position
│   ├── EvidenceReference.php      # Value object: chunk_id, document_id, excerpt
│   └── Contracts/
│       ├── DocumentRepositoryInterface.php
│       └── ChunkRepositoryInterface.php
├── Approval/
│   ├── ApprovalRequest.php        # Entity: workflow_run_id, step, action, status
│   ├── ApprovalStatus.php
│   └── Contracts/
│       └── ApprovalRepositoryInterface.php
└── Shared/
    ├── Entity.php                 # Base entity (id, timestamps) if needed
    └── ValueObject.php            # Base for value objects
```

### app/Application/

Use cases, DTOs, and application services. Depends on Domain (and interfaces); no infrastructure details.

```
app/Application/
├── Workflow/
│   ├── Services/
│   │   └── WorkflowOrchestrator.php
│   ├── DTOs/
│   │   ├── WorkflowRunContext.php
│   │   └── StepExecutionResult.php
│   ├── Actions/
│   │   ├── StartWorkflowRun.php
│   │   ├── ExecuteWorkflowStep.php
│   │   └── ResumeWorkflowRun.php
│   └── Contracts/
│       └── WorkflowExecutionEngineInterface.php
├── Agent/
│   ├── Services/
│   │   └── AgentOrchestrationService.php
│   ├── DTOs/
│   │   ├── AgentContext.php
│   │   └── LLMRequest.php / LLMResponse.php
│   └── Contracts/
│       └── LLMProviderInterface.php
├── Tool/
│   ├── Services/
│   │   └── ToolGatewayService.php
│   ├── DTOs/
│   │   └── ToolExecutionRequest.php
│   └── Policies/
│       └── ToolApprovalPolicy.php
├── Document/
│   ├── Services/
│   │   └── DocumentIngestionService.php
│   ├── Actions/
│   │   ├── IngestUploadedDocument.php
│   │   └── RetrieveChunksForContext.php
│   └── DTOs/
│       └── IngestedDocumentResult.php
├── Approval/
│   ├── Actions/
│   │   ├── RequestApproval.php
│   │   └── ResolveApproval.php
│   └── Contracts/
│       └── ApprovalServiceInterface.php
└── Observability/
    ├── Services/
    │   └── WorkflowAuditLogger.php
    ├── DTOs/
    │   └── TokenUsageRecord.php
    └── Contracts/
        └── AuditLoggerInterface.php
```

### app/Infrastructure/

Implementations for persistence, LLM, tools, and external services.

```
app/Infrastructure/
├── Persistence/
│   ├── Eloquent/
│   │   ├── WorkflowRunModel.php
│   │   ├── WorkflowStepModel.php
│   │   ├── DocumentModel.php
│   │   ├── DocumentChunkModel.php
│   │   ├── ApprovalRequestModel.php
│   │   └── Repositories/          # Eloquent implementations of Domain/Application contracts
│   └── Migrations/                # Optional: if not under database/migrations
├── LLM/
│   ├── OpenAI/
│   │   └── OpenAILMAdapter.php    # Implements LLMProviderInterface
│   └── LLMResponseParser.php      # Parses tool calls from response
├── Tool/
│   ├── ToolExecutionGateway.php   # Implements ToolExecutionGatewayInterface
│   ├── ToolRegistry.php           # Allowlist, schema, approval policy per tool
│   └── Executors/                 # Concrete tool implementations (e.g. SearchChunks, SendNotification)
├── Document/
│   ├── Parsers/
│   │   ├── PdfParser.php
│   │   ├── DocxParser.php
│   │   └── TextParser.php
│   ├── Chunking/
│   │   └── ChunkingStrategy.php
│   ├── Embedding/
│   │   └── OpenAIEmbeddingProvider.php
│   └── VectorStore/
│       └── PgVectorStore.php      # Queries pgvector
├── Approval/
│   └── ApprovalService.php        # Implements ApprovalServiceInterface
└── Observability/
    ├── AuditLogger.php            # Writes to DB and/or log channel
    └── CostEstimator.php         # Maps provider/model to cost per token
```

### app/Http/

Laravel HTTP layer: thin controllers, form requests, API resources.

```
app/Http/
├── Controllers/
│   ├── Api/
│   │   ├── WorkflowRunController.php
│   │   ├── DocumentController.php
│   │   ├── ApprovalController.php
│   │   └── LogsController.php
│   └── Web/
│       ├── DashboardController.php
│       ├── WorkflowRunController.php
│       └── ApprovalController.php
├── Requests/
├── Resources/
│   ├── WorkflowRunResource.php
│   ├── DocumentResource.php
│   └── ApprovalRequestResource.php
└── Middleware/
```

## config/

- **config/ai-workflow.php** — Workflow definitions (first workflow: document analysis), agent profiles, tool allowlists, approval policies.
- **config/llm.php** — Provider, model, API key reference, timeouts.
- **config/tools.php** — Tool definitions (name, schema, requires_approval).

## routes/

- **api.php** — REST API for tasks, workflow runs, documents, approvals, logs.
- **web.php** — Auth and minimal UI routes (dashboard, run detail, approval actions).

## tests/

```
tests/
├── Unit/
│   ├── Domain/
│   ├── Application/
│   └── Infrastructure/
├── Feature/
│   ├── Api/
│   └── Workflow/
├── Integration/
│   └── WorkflowExecutionTest.php
└── Fixtures/
    ├── WorkflowFixtures.php
    └── MockLLMResponses.php
```

## Summary

- **Domain**: entities, value objects, domain events, repository/contract interfaces.
- **Application**: use cases, services, DTOs, application-level contracts (LLM, gateway, approval, audit).
- **Infrastructure**: Eloquent models, repositories, LLM adapter, tool gateway and executors, document parsers, vector store, approval and audit implementations.
- **Interface**: Laravel HTTP (API + minimal web UI).

This keeps domain and application logic testable without the framework and allows swapping infrastructure (e.g. another LLM provider or vector store) without changing use cases or domain.
