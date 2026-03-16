# PostgreSQL Schema and Migration Plan

## Extensions

- **uuid-ossp** or use Laravel's `ulid()` for UUIDs/ULIDs.
- **pgvector** for `embedding` column on `document_chunks`.

## Migration Order

1. Enable extensions (uuid-ossp if needed, pgvector).
2. `workflows` (templates).
3. `workflow_runs` (run instances).
4. `workflow_steps` (steps per run).
5. `agents` (role profiles).
6. `documents`.
7. `document_chunks` (with vector column).
8. `approval_requests`.
9. `tool_definitions` (optional; can be config-only in V1).
10. Audit/observability tables: `workflow_run_logs`, `llm_call_logs`, `tool_execution_logs`.

## Table Definitions

### workflows

| Column       | Type         | Notes                    |
|-------------|--------------|--------------------------|
| id          | uuid/ulid PK |                          |
| name        | string       |                          |
| slug        | string unique| e.g. document_analysis   |
| description | text nullable|                          |
| definition  | jsonb        | steps, approval_step_ids |
| created_at  | timestamp    |                          |
| updated_at  | timestamp    |                          |

### workflow_runs

| Column            | Type         | Notes                          |
|-------------------|--------------|--------------------------------|
| id                | uuid/ulid PK |                                |
| workflow_id       | FK workflows |                                |
| status            | string       | workflow_status enum           |
| context           | jsonb        | input payload                  |
| current_step_id   | FK nullable  | workflow_steps.id              |
| started_at        | timestamp nullable |                         |
| completed_at      | timestamp nullable |                         |
| created_at        | timestamp    |                                |
| updated_at        | timestamp    |                                |
| user_id           | FK nullable  | submitter (if using users table) |

### workflow_steps

| Column          | Type         | Notes                |
|-----------------|--------------|----------------------|
| id              | uuid/ulid PK |                      |
| workflow_run_id | FK workflow_runs |                  |
| step_key        | string       | plan, analyse, etc.  |
| order           | integer      |                      |
| status          | string       | step_status enum     |
| input_payload   | jsonb nullable |                   |
| output_payload  | jsonb nullable |                   |
| retry_count     | integer default 0 |               |
| max_retries     | integer default 3 |               |
| requires_approval | boolean default false |           |
| started_at      | timestamp nullable |                 |
| completed_at    | timestamp nullable |                 |
| created_at      | timestamp    |                      |
| updated_at      | timestamp    |                      |

### agents

| Column             | Type    | Notes                |
|--------------------|---------|----------------------|
| id                 | uuid/ulid PK |                 |
| role               | string  | planner, analyst, etc. |
| name               | string  |                     |
| system_prompt      | text    |                     |
| allowed_tool_names | jsonb   | array of strings    |
| default_model      | string nullable |     |
| created_at         | timestamp |                   |
| updated_at         | timestamp |                   |

### documents

| Column          | Type         | Notes           |
|-----------------|--------------|-----------------|
| id              | uuid/ulid PK |                 |
| name            | string       |                 |
| original_name   | string       |                 |
| mime_type       | string       |                 |
| storage_path    | string       |                 |
| status          | string       | uploaded, etc.  |
| meta            | jsonb nullable |               |
| user_id         | FK nullable  |                 |
| workflow_run_id | FK nullable  |                 |
| created_at      | timestamp    |                 |
| updated_at      | timestamp    |                 |

### document_chunks

| Column      | Type         | Notes              |
|-------------|--------------|--------------------|
| id          | uuid/ulid PK |                    |
| document_id | FK documents |                    |
| content     | text         |                    |
| position    | integer      | order in document  |
| token_count | integer nullable |               |
| embedding   | vector(n)    | pgvector; n=1536 for OpenAI | 
| meta        | jsonb nullable |                  |
| created_at  | timestamp    |                    |
| updated_at  | timestamp    |                    |

Index: `document_id`, and pgvector index on `embedding` for similarity search (e.g. ivfflat or hnsw).

### approval_requests

| Column           | Type         | Notes          |
|------------------|--------------|----------------|
| id               | uuid/ulid PK |                |
| workflow_run_id  | FK workflow_runs |             |
| step_id          | FK workflow_steps |             |
| tool_name        | string       |                |
| tool_arguments   | jsonb        | sanitized      |
| status           | string       | pending, etc.  |
| requested_at     | timestamp    |                |
| resolved_at      | timestamp nullable |            |
| resolved_by      | FK nullable  | user_id        |
| comment          | text nullable |               |
| created_at       | timestamp    |                |
| updated_at       | timestamp    |                |

### workflow_run_logs (observability)

| Column        | Type      | Notes              |
|---------------|-----------|--------------------|
| id            | bigserial PK |                  |
| workflow_run_id | FK       |                    |
| step_id       | FK nullable |                   |
| event_type    | string    | step_started, etc. |
| payload       | jsonb nullable |                |
| created_at    | timestamp |                    |

### llm_call_logs

| Column         | Type      | Notes           |
|----------------|-----------|-----------------|
| id             | bigserial PK |               |
| workflow_run_id| FK        |                 |
| step_id        | FK nullable |                |
| provider       | string    |                 |
| model          | string    |                 |
| input_tokens   | integer   |                 |
| output_tokens  | integer   |                 |
| latency_ms     | integer nullable |          |
| estimated_cost | decimal nullable |          |
| created_at     | timestamp |                 |

### tool_execution_logs

| Column         | Type      | Notes     |
|----------------|-----------|-----------|
| id             | bigserial PK |          |
| workflow_run_id| FK        |           |
| step_id        | FK nullable |          |
| tool_name      | string    |           |
| request_snapshot | jsonb nullable |     |
| result_snapshot  | jsonb nullable |     |
| approved       | boolean nullable |      |
| created_at     | timestamp |           |

## Indexes

- `workflow_runs`: workflow_id, status, user_id, created_at.
- `workflow_steps`: workflow_run_id, order, status.
- `document_chunks`: document_id; vector index on embedding (ivfflat or hnsw).
- `approval_requests`: workflow_run_id, status.
- `workflow_run_logs`, `llm_call_logs`, `tool_execution_logs`: workflow_run_id, created_at.

## Users Table

If using Laravel auth: keep `users` table as per Laravel default. Reference `user_id` in workflow_runs, documents, approval_requests.resolved_by.
