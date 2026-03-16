# API Overview (V1)

REST API for workflow runs, documents, approvals, and logs. All endpoints are subject to authentication (Laravel Sanctum) and authorization in production.

## Base URL

`/api/v1` (or `/api` with versioning header).

## Endpoints (to implement)

| Method | Path | Description |
|--------|------|-------------|
| POST | /workflow-runs | Create a new workflow run (body: workflow_slug, context, document_ids, user_question) |
| GET | /workflow-runs/{id} | Get run with steps and outputs |
| GET | /workflow-runs | List runs (filter by status, user, workflow) |
| POST | /documents/upload | Upload one or more documents (multipart) |
| GET | /documents/{id} | Get document metadata and status |
| GET | /approvals/pending | List pending approval requests |
| POST | /approvals/{id}/approve | Approve (body: comment optional) |
| POST | /approvals/{id}/reject | Reject (body: comment optional) |
| GET | /workflow-runs/{id}/logs | Get execution and LLM/tool logs for a run |

## Response format

- Success: 200/201 with JSON body (resource or collection).
- Validation error: 422 with `message` and `errors`.
- Not found: 404.
- Auth: 401/403 as appropriate.

## Authentication

Use Laravel Sanctum: API tokens or session-based auth for the minimal web UI. Document in OpenAPI/Swagger when implemented.
