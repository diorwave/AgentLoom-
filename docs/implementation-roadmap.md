# Phased Implementation Roadmap (V1)

## Phase 1: Foundation (Current)

- [x] Architecture and docs (architecture, directory structure, domain model, workflow design, tool gateway, document pipeline, migration plan).
- [ ] Laravel project creation (PHP 8.3+, Laravel 11).
- [ ] Directory structure: `app/Domain`, `Application`, `Infrastructure` with placeholder namespaces.
- [ ] PostgreSQL + Redis + pgvector in Docker.
- [ ] Core migrations: workflows, workflow_runs, workflow_steps, agents, documents, document_chunks (with vector), approval_requests, observability tables.
- [ ] Domain entities and value objects (Workflow, WorkflowRun, WorkflowStep, status enums; Agent; Document, DocumentChunk; ApprovalRequest; Tool value objects).
- [ ] Repository interfaces (Domain/Application contracts) and Eloquent implementations (stub or minimal).
- [ ] Config: `config/ai-workflow.php` (workflow definition for document_analysis), `config/llm.php`, `config/tools.php`.

**Exit criteria**: App boots, migrations run, domain models exist, interfaces defined, config in place.

---

## Phase 2: Workflow Engine Core

- [ ] Workflow execution engine: load workflow definition, create WorkflowRun, create WorkflowSteps, advance state (pending → running → completed/failed).
- [ ] Step executor skeleton: execute one step by step_key; persist input/output and status.
- [ ] Resumability: load run by id, determine next step, execute, persist.
- [ ] Retry: on step failure, increment retry_count; if retry_count < max_retries, set status retry_scheduled and dispatch retry job; else set run to failed.
- [ ] Application actions: StartWorkflowRun, ExecuteWorkflowStep, ResumeWorkflowRun (and queue job for async step execution if desired).
- [ ] Unit/feature tests for state transitions and step ordering.

**Exit criteria**: A run can be started, steps executed in order, state persisted and resumable; retry path works.

---

## Phase 3: LLM Abstraction and First Agent

- [ ] LLMProviderInterface (Application): method to send messages and optional tool definitions; return response + parsed tool calls.
- [ ] OpenAI adapter: implement interface using OpenAI API (chat completions, function/tool calling).
- [ ] Agent profile loading: map step_key → agent role, load system prompt and allowed tools from config/DB.
- [ ] Agent orchestration service: build prompt (system + user/context), call LLM, parse response; no tool execution yet (or mock).
- [ ] Integrate into workflow: “plan” step calls planner agent with document metadata; store plan in step output.
- [ ] Observability: log LLM request/response (truncated/hashed if needed), token usage, latency, estimated cost (LLMCallLog).

**Exit criteria**: Document-analysis workflow can run “plan” step with real or mocked LLM; plan stored; logs written.

---

## Phase 4: Document Pipeline and RAG

- [ ] Document upload API: store file, create Document record, dispatch ingestion job.
- [ ] Parsers: PDF, DOCX, TXT (and optionally HTML); normalize text.
- [ ] Chunking: fixed-size + overlap strategy; create DocumentChunk records.
- [ ] Embedding: OpenAI embeddings adapter; embed chunks, store in document_chunks.embedding.
- [ ] PgVector: similarity search (query embedding → top-k chunks); expose as ChunkRepository or VectorStore interface.
- [ ] Ingest step in workflow: after upload, run ingestion (or wait for job); pass document_ids and chunk counts to “plan”.
- [ ] RetrieveChunksForContext: used by “analyse” step to get chunks by query (from plan or user question); attach EvidenceReference in step output.
- [ ] Tests: unit tests for parsers and chunking; integration test for embed + search.

**Exit criteria**: Upload → parse → chunk → embed → search works; workflow ingest step completes; RAG available for analyse step.

---

## Phase 5: Tool Gateway and Approval

- [ ] ToolExecutionGatewayInterface and implementation: allowlist, schema validation, approval check, execute, log.
- [ ] Tool registry: load from config; ToolDefinition (name, schema, requires_approval).
- [ ] At least one tool executor (e.g. retrieve_chunks) that calls vector store and returns chunks.
- [ ] Approval flow: gateway creates ApprovalRequest when tool requires approval; run/step awaiting_approval; API to list/approve/reject; on approve, gateway executes and step continues.
- [ ] Wire analyst step to use RAG (via tool or direct call) and optionally call gateway for tools; store evidence references in output.
- [ ] Tool execution logs and approval audit.

**Exit criteria**: Tool calls go through gateway; approval blocks execution until resolved; evidence refs stored.

---

## Phase 6: Full Document-Analysis Workflow and UI

- [ ] All five steps implemented: ingest → plan → analyse → review → summarize.
- [ ] Reviewer and summarizer agents: prompts and integration; pass findings and review result into summarizer.
- [ ] Final output stored on run and step; API returns run with steps and output.
- [ ] Minimal web UI: login, upload documents, start workflow, view run status and steps, view approval queue, approve/reject, view logs (and optionally token/cost).
- [ ] API documentation (OpenAPI or Markdown) for REST endpoints.
- [ ] End-to-end test: upload doc → run workflow → assert final report and evidence refs present.

**Exit criteria**: Full workflow runs end-to-end; UI supports submit, track, approve, inspect logs; API documented.

---

## Phase 7: Hardening and Delivery

- [ ] Error handling and retries across pipeline and workflow; clear failure reasons in run/step.
- [ ] Rate limiting and validation on API; secure API key handling; RBAC where needed.
- [ ] Deployment guide: Docker, env vars, DB migrations, queue worker, pgvector.
- [ ] Test suite summary: unit, feature, integration, one E2E workflow test.
- [ ] Sample workflow config and README with “how to run” and “how to add a workflow”.

**Exit criteria**: Production-ready foundation; docs and tests in place; deployable via Docker.

---

## Dependency Summary

- Phase 2 depends on Phase 1.
- Phase 3 depends on Phase 2 (engine runs steps; one step is LLM).
- Phase 4 can start in parallel with Phase 3 after Phase 1 (doc pipeline independent of LLM).
- Phase 5 depends on Phase 3 (gateway used by agent orchestration) and Phase 4 (retrieve_chunks needs RAG).
- Phase 6 depends on Phases 2–5.
- Phase 7 is final polish.

Current focus: **Phase 1** — complete scaffolding, migrations, domain models, and config.
