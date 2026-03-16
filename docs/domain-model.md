# Core Domain Model and Workflow State

## 1. Workflow Engine Entities

### Workflow (Template)

- **Identity**: `id` (UUID or ULID).
- **Attributes**: `name`, `slug`, `description`, `definition` (structured: steps, transitions, approval_step_ids).
- **Purpose**: Reusable template. No runtime state; only definition.

### WorkflowRun (Aggregate Root)

- **Identity**: `id`.
- **Attributes**:
  - `workflow_id` (FK to workflow template).
  - `status`: `WorkflowStatus` (pending | running | completed | failed | awaiting_approval | retry_scheduled).
  - `context`: JSON or DTO — input payload (e.g. document_ids, user_question, user_id).
  - `current_step_index` or `current_step_id` (for resumable execution).
  - `started_at`, `completed_at`, `approved_at` (optional).
- **Invariants**: Status transitions are constrained (e.g. running → completed | failed | awaiting_approval).

### WorkflowStep (Entity within Run)

- **Identity**: `id`; belongs to one `WorkflowRun`.
- **Attributes**:
  - `step_key` (e.g. `plan`, `analyse`, `review`, `summarize`) — from workflow definition.
  - `order` (integer).
  - `status`: `StepStatus` (pending | running | completed | failed | skipped | awaiting_approval).
  - `input_payload`: JSON (context passed into the step).
  - `output_payload`: JSON (result of the step; evidence refs, plan, report, etc.).
  - `retry_count`, `max_retries`.
  - `requires_approval`: boolean (from definition).
  - `started_at`, `completed_at`.
- **Invariants**: Only one step per run is typically "running" at a time (or "awaiting_approval").

### WorkflowStatus (Value Object / Enum)

```text
pending | running | completed | failed | awaiting_approval | retry_scheduled
```

### StepStatus (Value Object / Enum)

```text
pending | running | completed | failed | skipped | awaiting_approval
```

---

## 2. Agent

### Agent (Profile)

- **Identity**: `id`.
- **Attributes**: `role` (AgentRole: planner | analyst | reviewer | summarizer), `name`, `system_prompt`, `allowed_tool_names` (array), `default_model` (optional).
- **Purpose**: Role-based profile used by the orchestration layer to build prompts and restrict tools.

### AgentRole (Enum)

```text
planner | analyst | reviewer | summarizer
```

---

## 3. Tool Gateway

### ToolDefinition

- **Identity**: `name` (string, unique).
- **Attributes**: `schema` (JSON Schema for arguments), `requires_approval` (boolean), `description` (for LLM).

### ToolCallRequest (Value Object)

- **Attributes**: `tool_name`, `arguments` (array/object), `requested_by_agent`, `workflow_run_id`, `step_id`.

### ToolCallResult (Value Object)

- **Attributes**: `success`, `output` (string or structured), `error_message` (if failed), `approved_by` (nullable), `executed_at`.

---

## 4. Document and RAG

### Document

- **Identity**: `id`.
- **Attributes**: `name`, `original_name`, `mime_type`, `storage_path`, `status` (uploaded | parsing | chunking | embedded | failed), `meta` (JSON), `user_id` (uploader), `workflow_run_id` (optional; if scoped to a run).

### DocumentChunk

- **Identity**: `id`.
- **Attributes**: `document_id`, `content` (text), `position` (order in document), `token_count` (optional), `embedding` (vector; stored in DB via pgvector). Metadata for provenance.

### EvidenceReference (Value Object)

- **Attributes**: `chunk_id`, `document_id`, `document_name`, `excerpt` (optional short snippet), `score` (optional similarity).

---

## 5. Approval

### ApprovalRequest

- **Identity**: `id`.
- **Attributes**: `workflow_run_id`, `step_id`, `tool_name`, `tool_arguments` (sanitized JSON), `status` (pending | approved | rejected), `requested_at`, `resolved_at`, `resolved_by` (user_id), `comment` (optional).

### ApprovalStatus (Enum)

```text
pending | approved | rejected
```

---

## 6. Observability (Logical Model)

- **WorkflowRunLog** / **StepExecutionLog**: run_id, step_id, event_type (e.g. step_started, step_completed, tool_requested), payload (JSON; optional truncation), created_at.
- **LLMCallLog**: run_id, step_id, provider, model, input_tokens, output_tokens, latency_ms, estimated_cost, created_at.
- **ToolExecutionLog**: run_id, step_id, tool_name, request_snapshot, result_snapshot, approved (bool), created_at.

These can be separate tables or one `audit_log` table with event_type and payload.

---

## 7. State Transitions (Workflow Run)

```text
pending → running (when first step starts)
running → awaiting_approval (when step requires approval and gateway creates ApprovalRequest)
awaiting_approval → running (when approval granted)
running → completed (all steps done)
running → failed (step failed and no more retries / policy says fail)
running → retry_scheduled (step failed, retry scheduled)
retry_scheduled → running (when retry job runs)
```

Step-level transitions:

```text
pending → running
running → completed | failed | awaiting_approval
awaiting_approval → running (after approval)
failed → running (on retry)
```

---

## 8. Summary Diagram (Conceptual)

```text
Workflow (1) ──< WorkflowRun (N)
                     │
                     ├── WorkflowStep (N)  [plan, analyse, review, summarize]
                     ├── ApprovalRequest (0..N)
                     └── Logs / LLMCallLog / ToolExecutionLog

Document (N) ──< DocumentChunk (N)   [embedding in pgvector]
     │
     └── referenced by EvidenceReference in step outputs

Agent (N) — used by WorkflowStep (role per step)
ToolDefinition (N) — used by ToolExecutionGateway (allowlist, schema, approval)
```

This domain model supports the first V1 workflow (document upload → plan → analyse → review → summarize) and leaves room for more workflows, tools, and agents.
