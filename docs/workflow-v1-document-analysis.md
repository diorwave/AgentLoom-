# First V1 Workflow: Document Analysis

## Overview

Single end-to-end workflow: **document upload → planning → evidence extraction → review → summary**, with full traceability, optional approval, and observability.

## Step Definitions

| Order | Step key    | Agent role  | Description |
|-------|-------------|-------------|-------------|
| 1     | ingest      | (system)    | Parse, chunk, embed, store documents. No LLM. |
| 2     | plan        | planner     | Produce analysis plan from document metadata + optional user question. |
| 3     | analyse     | analyst     | Retrieve relevant chunks (RAG), extract evidence and findings. |
| 4     | review      | reviewer    | Validate quality/consistency/sufficiency of findings. |
| 5     | summarize   | summarizer  | Produce final report from reviewed findings. |

## Data Flow

1. **Input**: User uploads one or more files (PDF, DOCX, TXT, HTML); optional free-text question or goal.
2. **ingest**: Document pipeline runs (parse → chunk → embed → store). Output: `document_ids`, `chunk_counts`. Stored in run context and step output.
3. **plan**: Planner receives document names, chunk counts, user question. Output: structured plan (e.g. sections, questions to answer). Stored in step output and passed to next step.
4. **analyse**: Analyst receives plan + RAG retrieval result (top-k chunks). Allowed tools: e.g. `retrieve_chunks` (or retrieval is done by app and passed in). Output: findings + evidence references (chunk IDs, document IDs, excerpts). Stored in step output.
5. **review**: Reviewer receives findings and evidence refs. Output: validation result (approved / amendments). If amendments, can re-run analyse or store amendments for summarizer. Stored in step output.
6. **summarize**: Summarizer receives plan, findings, review result. Output: final report (text or structured). Stored in step output and as final run output.

## Evidence Traceability

- **analyse** step output must include an array of **EvidenceReference** (chunk_id, document_id, excerpt).
- These are persisted with the step output and can be shown in UI/API for citations.
- Retrieval queries (and top-k results) can also be logged for audit.

## Approval Checkpoints

- For V1, no step is required to have `requires_approval` by default.
- If a **sensitive tool** is added (e.g. "send_email", "export_data"), the tool definition sets `requires_approval = true`. When the analyst or any agent requests that tool, the Tool Gateway creates an `ApprovalRequest` and sets the run to `awaiting_approval`. After human approval, the tool runs and the workflow resumes.

## Resumability

- After each step (and after approval), run and step state are persisted.
- On failure: step status = failed; run status = failed or retry_scheduled. A retry job can re-execute the same step (with optional backoff).
- On approval: run status = awaiting_approval until approval; then step completes and next step runs.

## Configuration (Structured)

Workflow is defined in config or code, e.g.:

- **Workflow key**: `document_analysis`.
- **Steps**: ingest → plan → analyse → review → summarize.
- **Step config**: agent_role per step (null for ingest), allowed_tools per step, max_retries, requires_approval (optional).

This workflow exercises: workflow engine, multi-agent orchestration, RAG, tool gateway (if analyst has a tool), evidence refs, observability, and approval path.
