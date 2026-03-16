# First Implementation Steps (V1 Foundation)

This document summarises what has been implemented in the initial scaffold and the immediate next steps.

## Done in this scaffold

1. **Architecture and design docs**
   - `docs/architecture.md` — high-level architecture, subsystems, security, stack.
   - `docs/directory-structure.md` — Laravel project structure (Domain, Application, Infrastructure, Interface).
   - `docs/domain-model.md` — entities, value objects, workflow state, state transitions.
   - `docs/workflow-v1-document-analysis.md` — first workflow definition and data flow.
   - `docs/tool-execution-gateway.md` — gateway design and security.
   - `docs/document-pipeline.md` — ingestion and RAG design.
   - `docs/migration-plan.md` — PostgreSQL schema and migration order.
   - `docs/implementation-roadmap.md` — phased roadmap (Phases 1–7).
   - `docs/api-overview.md` — planned REST API.

2. **Laravel project**
   - `composer.json` — PHP 8.3, Laravel 11, OpenAI client, Pest; PSR-4 autoload for `App\`.
   - `bootstrap/app.php`, `config/app.php`, `config/database.php` — bootstrap and DB (PostgreSQL default).
   - `routes/web.php`, `routes/api.php`, `routes/console.php`.
   - `app/Providers/AppServiceProvider`, `AuthServiceProvider`.
   - `.env.example` — app, DB, Redis, LLM, workflow config.

3. **Domain layer**
   - **Workflow**: `Workflow`, `WorkflowRun`, `WorkflowStep`, `WorkflowStatus`, `StepStatus`, `WorkflowRepositoryInterface`.
   - **Agent**: `Agent`, `AgentRole`, `AgentProfileRepositoryInterface`.
   - **Tool**: `ToolDefinition`, `ToolCallRequest`, `ToolCallResult`, `ToolExecutionGatewayInterface`.
   - **Document**: `Document`, `DocumentChunk`, `DocumentStatus`, `EvidenceReference`, `DocumentRepositoryInterface`, `ChunkRepositoryInterface`.
   - **Approval**: `ApprovalRequest`, `ApprovalStatus`, `ApprovalRepositoryInterface`.

4. **Application layer**
   - **Agent**: `LLMProviderInterface`, `LLMRequest`, `LLMResponse` DTOs.
   - **Observability**: `AuditLoggerInterface`.

5. **Infrastructure**
   - `ConfigWorkflowRepository` — reads workflow templates from `config/ai-workflow.php`; bound to `WorkflowRepositoryInterface`.
   - Persistence for runs/steps (DB) is left for Phase 2.

6. **Config**
   - `config/ai-workflow.php` — document_analysis workflow and agent profiles.
   - `config/llm.php` — OpenAI provider, logging, cost estimation.
   - `config/tools.php` — tool definitions (e.g. `retrieve_chunks`) and approval policy.

7. **Database**
   - Migrations: pgvector extension, users, workflows, workflow_runs, workflow_steps, agents, documents, document_chunks (with vector column), approval_requests, workflow_run_logs, llm_call_logs, tool_execution_logs.
   - Run `php artisan migrate` after `composer install` and DB setup.

8. **Docker**
   - `Dockerfile` — PHP 8.3, Composer, pdo_pgsql.
   - `docker-compose.yml` — app, queue, postgres (pgvector), redis.

9. **Tests**
   - `tests/Unit/Domain/Workflow/WorkflowStatusTest.php` — status transitions.
   - `tests/Unit/Domain/Workflow/WorkflowTest.php` — workflow steps and approval keys.
   - `tests/Feature/WorkflowConfigTest.php` — resolve workflow by slug from config.
   - `phpunit.xml` — unit and feature suites.

10. **README**
    - Quick start (local and Docker), docs index, structure summary, first workflow, security, testing.

## Next steps (Phase 2)

- Implement **WorkflowExecutionEngine**: create `WorkflowRun` and `WorkflowStep` records, persist to DB (Eloquent models and repository).
- Implement **StartWorkflowRun** and **ExecuteWorkflowStep** actions; wire to queue job for async step execution.
- Add **resumability**: load run by id, determine next step, execute, update status.
- Add **retry**: on step failure, increment retry_count; schedule retry or mark run failed.
- Add **observability**: `AuditLogger` implementation writing to `workflow_run_logs`.
- Unit and feature tests for state transitions and step ordering.

After Phase 2, proceed to Phase 3 (LLM abstraction and first agent), then Phase 4 (document pipeline and RAG), Phase 5 (tool gateway and approval), and Phase 6 (full workflow + minimal UI).
