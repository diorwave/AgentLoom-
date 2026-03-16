# AI Workflow Platform

Production-grade AI orchestration and workflow platform in Laravel (PHP 8.3+). Multi-step workflows, role-based agents, secure tool execution, document RAG, approvals, and observability.

**This is not a chatbot or thin OpenAI wrapper.** It is an orchestration and control platform with structured workflows, secure tool execution, document processing, approvals, and traceability.

## Documentation

- [Architecture overview](docs/architecture.md)
- [Directory structure](docs/directory-structure.md)
- [Domain model](docs/domain-model.md)
- [First V1 workflow (document analysis)](docs/workflow-v1-document-analysis.md)
- [Tool execution gateway](docs/tool-execution-gateway.md)
- [Document pipeline](docs/document-pipeline.md)
- [Migration plan (PostgreSQL)](docs/migration-plan.md)
- [Implementation roadmap](docs/implementation-roadmap.md)

## Requirements

- PHP 8.3+ with extensions: `json`, `mbstring`, `xml` (and for production: `pdo_pgsql`, `openssl`, etc.)
  - On Debian/Ubuntu: `sudo apt install php8.3-xml php8.3-mbstring php8.3-pgsql`
- Composer
- PostgreSQL (with [pgvector](https://github.com/pgvector/pgvector) for embeddings)
- Redis (queues and cache)

## Quick start (local)

```bash
cp .env.example .env
# Edit .env: set DB_*, REDIS_*, OPENAI_API_KEY

composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

## Quick start (Docker)

```bash
cp .env.example .env
# Set OPENAI_API_KEY and any overrides

docker compose build app
docker compose up -d postgres redis
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate
docker compose up -d app queue
```

API: `http://localhost:8000`

## Project structure (summary)

- **app/Domain** — Workflow, Agent, Tool, Document, Approval entities and contracts (framework-agnostic).
- **app/Application** — Use cases, DTOs, LLM/tool/approval/audit interfaces.
- **app/Infrastructure** — Eloquent, LLM adapters, tool gateway, document parsers, vector store.
- **config/ai-workflow.php** — Workflow and agent definitions.
- **config/llm.php** — LLM provider and logging.
- **config/tools.php** — Tool definitions and approval policy.

## First workflow: Document Analysis

1. User uploads documents.
2. Ingest: parse → chunk → embed → store (pgvector).
3. Planner: analysis plan from metadata + optional question.
4. Analyst: evidence extraction from RAG chunks; evidence references stored.
5. Reviewer: validate findings.
6. Summarizer: final report.
7. Logs, token usage, cost, and evidence traceability throughout.

## Security

- Tool execution only via gateway: allowlist, schema validation, optional human approval.
- Uploaded documents are untrusted; they do not affect system instructions or permissions.
- Prompt-injection-aware design: system and user/document content are separated.

## Testing

```bash
composer test
```

Use mocks and fixtures for LLM and tools; add integration tests for workflow execution.

## License

MIT.
