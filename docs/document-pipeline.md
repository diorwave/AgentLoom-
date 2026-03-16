# Document Ingestion and Retrieval Pipeline

## Overview

Uploaded documents are **untrusted input**. They are parsed, normalized, chunked, embedded, and stored. They are never used to change system instructions, permissions, or tool policy. Document content is only ever supplied in clearly bounded context (e.g. "user" or "document" blocks) for RAG.

## Ingestion Stages

1. **Upload**: File stored (e.g. Laravel storage); `Document` record created with status `uploaded`.
2. **Parse**: By mime type, dispatch to parser (PDF, DOCX, TXT, HTML). Output: plain text (and optionally structure). Status → `parsing`, then `parsed` or `failed`.
3. **Normalize**: Clean text (encoding, excessive whitespace, control chars). No execution of content.
4. **Chunk**: Split into chunks (e.g. fixed size + overlap, or semantic). Store `DocumentChunk` with `document_id`, `content`, `position`.
5. **Embed**: For each chunk, call embedding provider (e.g. OpenAI). Store embedding in chunk record (pgvector column).
6. **Complete**: Document status → `embedded` (or `ready`). Chunks are queryable.

All stages are idempotent where possible; failures can retry from last successful stage.

## Storage (PostgreSQL + pgvector)

- **documents**: id, name, original_name, mime_type, storage_path, status, meta (JSONB), user_id, workflow_run_id (nullable), timestamps.
- **document_chunks**: id, document_id, content (text), position (int), token_count (nullable), embedding (vector(n)), meta (JSONB), timestamps.
- **pgvector**: Extension enabled; similarity search via `<=>` or `ORDER BY embedding <=> query_embedding LIMIT k`.

## Retrieval (RAG)

- **Input**: Query text (e.g. from planner output or user question), optional document_ids filter, top_k.
- **Process**: Embed query → search chunks (and optionally filter by document_id) → return ordered list of chunks with scores.
- **Output**: List of chunks with id, document_id, content, score; mapped to **EvidenceReference** for use in prompts and step outputs.
- **Traceability**: Chunk IDs and document IDs are always attached to step outputs that use them so evidence is citable.

## Parsers (V1)

- **PDF**: Use a library (e.g. smalot/pdfparser or pdftotext) to extract text. No execution of embedded scripts; text only.
- **DOCX**: Use PhpOffice/PhpWord or similar; text extraction only.
- **TXT / HTML**: Strip tags for HTML; use UTF-8 and normalize line endings for TXT.

## Security

- **No execution**: Parsing produces plain text only. No eval, no inclusion in system prompts in a way that could override instructions.
- **Bounds**: In the LLM request, document content is placed in separate messages or structured blocks labeled as document/user content, not system.
- **Size limits**: Enforce max file size and max total chunks per document to avoid abuse.
