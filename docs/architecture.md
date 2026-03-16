# AI Workflow Platform — Architecture Overview

## 1. Purpose and Scope

This system is an **AI orchestration and control platform**, not a chatbot or thin API wrapper. It provides:

- **Structured multi-step workflows** with defined stages (planning, analysis, retrieval, validation, reporting).
- **Role-based AI agents** (planner, analyst, reviewer, summarizer) within a single platform.
- **Secure tool execution** via a controlled gateway (no arbitrary model-driven tool execution).
- **Document processing and RAG** with traceable evidence and chunk provenance.
- **Human approval checkpoints** for sensitive actions.
- **Observability**: execution logs, token usage, latency, estimated cost, workflow history.

## 2. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           INTERFACE LAYER                                    │
│  REST API │ Minimal Web UI (tasks, workflows, approvals, logs, documents)   │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
┌─────────────────────────────────────────────────────────────────────────────┐
│                        APPLICATION LAYER                                     │
│  Use Cases: SubmitTask, RunWorkflow, ExecuteStep, RequestApproval, IngestDoc │
│  Services: WorkflowOrchestrator, ToolGateway, ApprovalService, CostTracker  │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DOMAIN LAYER                                       │
│  Workflow, WorkflowRun, Step, Agent, Tool, Document, Chunk, ApprovalRequest  │
│  Value Objects: WorkflowStatus, StepStatus, EvidenceReference                │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
┌─────────────────────────────────────────────────────────────────────────────┐
│                       INFRASTRUCTURE LAYER                                    │
│  PostgreSQL (incl. pgvector) │ Redis (queues, cache) │ LLM Provider Adapter │
│  Document Parser/Embedder │ Tool Executors │ Audit Logger                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

- **Domain**: entities, value objects, domain events; no framework dependencies.
- **Application**: use cases, application services, DTOs; orchestrates domain and infrastructure.
- **Infrastructure**: persistence, external APIs, queues; implements interfaces defined in domain/application.
- **Interface**: HTTP controllers, API resources, minimal UI; thin layer that delegates to application.

## 3. Core Subsystems

### 3.1 Workflow Engine

- **Workflow**: template defining steps, transitions, and optional approval points.
- **WorkflowRun**: a single execution instance; owns state (pending, running, completed, failed, awaiting_approval, retry_scheduled).
- **Step**: one step in a run; has status, input/output payloads, retry count, and optional approval requirement.
- **Execution**: steps are executed in order; state is persisted after each step so runs are **resumable**.
- **Retries**: configurable per step; on failure, step can be marked for retry and run resumed.
- **Approval checkpoints**: steps can require human approval before the next step runs; run state becomes `awaiting_approval`.

Workflow definitions are **config/code-driven** in V1 (no visual builder). Example: YAML or PHP config defining the document-analysis workflow (plan → analyse → review → summarize).

### 3.2 AI Agent Orchestration

- **Agent**: role-based profile (planner, analyst, reviewer, summarizer) with:
  - System prompt / instruction profile
  - Allowed tools (by name)
  - Permissions / scope
  - Expected output schema or format hints
- **Single orchestration framework**: all agents use the same LLM abstraction and tool gateway.
- **Context assembly**: for each step, the application builds context (retrieved chunks, prior step outputs, user input) and passes it to the correct agent via the LLM adapter.
- **Structured output**: where needed, responses are validated or parsed for downstream steps and evidence references.

### 3.3 Tool Execution Gateway

- **No direct tool execution by the model.** The LLM returns structured tool *requests* (name + arguments).
- **Gateway**:
  - Receives tool request from the application (after parsing LLM response).
  - Validates tool name against **allowlist** for the current agent and workflow.
  - Validates arguments against a **schema** (e.g. JSON Schema).
  - Checks **approval policy**: if the tool is sensitive, creates an approval request and stops until approved.
  - **Logs** request, policy result, and execution outcome.
  - Executes the tool via registered **tool executors** (implementations in infrastructure).
  - Returns result to the application for inclusion in the next prompt or step output.
- **Security**: document content and user text never alter which tools are allowed or approval policy; that is defined by workflow and agent configuration.

### 3.4 Document Ingestion and RAG

- **Ingestion**: upload → parse (PDF, DOCX, TXT, HTML) → normalize text → chunk (strategy: by size/overlap or semantic) → embed → store (PostgreSQL + pgvector).
- **Metadata**: each chunk is tied to a document and position; provenance is always available.
- **Retrieval**: during workflow steps, the application queries the vector store (e.g. by embedding similarity), gets ranked chunks, and passes them to the agent as context. Chunk IDs and document references are kept for **evidence traceability**.
- **Untrusted input**: documents are never executed as code; they are not mixed into system prompts in a way that can override permissions or tool policy. They appear only in user/context blocks with clear boundaries.

### 3.5 Approval System

- **ApprovalRequest**: links to a workflow run and step, describes the pending action (e.g. tool name + sanitized args), status (pending / approved / rejected).
- **Checkpoint**: when the gateway determines that a tool requires approval, it creates an ApprovalRequest and sets the run (or step) to awaiting_approval. A human approves or rejects via API/UI; on approve, the gateway executes the tool and the workflow continues; on reject, the run can fail or retry according to policy.

### 3.6 Observability and Audit

- **Execution logs**: every workflow run, step execution, tool request, and tool execution is logged.
- **Prompt/output logging**: configurable; can log hashes or truncated content to avoid storing full PII; full content only in secure/audit storage if required.
- **Token usage**: provider, model, input/output tokens, latency, and **estimated cost** per call; aggregated per run and per project/user.
- **Queryable**: logs queryable by workflow_run_id, user_id, project_id, time range.

## 4. Security Principles

- **Architectural boundaries**: tool allowlists, permissions, and approval policy are enforced in code (gateway and workflow config), not only in prompts.
- **Document isolation**: uploaded documents are untrusted; they do not influence system instructions, tool allowlists, or permissions.
- **Prompt-injection awareness**: system instructions and agent prompts are kept separate from document and user content; context is structured so that user/document content is clearly delineated (e.g. separate message blocks or structured fields).
- **API keys**: stored in config/env, never in documents or user input; provider client used only in infrastructure layer.
- **RBAC**: authentication and role-based access for API and UI; audit logs for sensitive events (approvals, tool executions, access to logs).

## 5. Technology Stack (V1)

| Concern           | Choice        | Notes                                      |
|------------------|---------------|--------------------------------------------|
| Framework        | Laravel       | Default; supports queues, auth, API, jobs  |
| PHP              | 8.3+          |                                            |
| Database         | PostgreSQL    | Preferred; JSON, robust, pgvector          |
| Vector storage   | pgvector      | Embeddings and similarity search           |
| Queue / cache    | Redis         | Queues, cache, transient coordination      |
| Containerization | Docker        | docker-compose for local/dev               |
| LLM              | Behind interface | OpenAI-compatible adapter; swappable    |
| Testing          | PHPUnit / Pest| Unit, feature, integration, workflow tests |

## 6. First V1 Workflow: Document Analysis

1. **User** uploads one or more documents.
2. **System** parses, chunks, embeds, and stores them (document pipeline).
3. **Planner** agent: receives document metadata and optional user question; produces an analysis plan (e.g. sections to analyse, questions to answer).
4. **Analyst** agent: receives plan + retrieved chunks (RAG); extracts evidence and structured findings.
5. **Reviewer** agent: receives findings; validates quality, consistency, sufficiency; may request re-analysis or approve.
6. **Summarizer** agent: produces final report/output from reviewed findings.
7. **System** stores: workflow run, step outputs, evidence references (chunk IDs, document IDs), logs, token/cost data. Sensitive tools (if any) go through the approval gateway.

This workflow exercises: workflow engine, multiple agents, RAG retrieval, evidence traceability, observability, and optional approval if a sensitive tool is introduced later.

## 7. Out of Scope for V1

- Drag-and-drop workflow builder (config/code-only definitions).
- Multiple LLM providers in parallel (single provider behind abstraction is enough).
- Public-facing chat UI (internal/admin and API-first).

---

*This document is the single source of truth for the high-level architecture. Detailed design for each subsystem is expanded in implementation docs and code.*
