# Tool Execution Gateway — Design

## Principle

The LLM **never** executes tools directly. It returns **structured tool requests** (name + arguments). All execution goes through a **Tool Execution Gateway** that enforces allowlists, schema, approval policy, and logging.

## Flow

1. Application gets LLM response; parses out tool calls (e.g. from OpenAI function_call or tool_use format).
2. For each tool call, application calls the **Tool Gateway** with: `tool_name`, `arguments`, `agent_role`, `workflow_run_id`, `step_id`.
3. Gateway:
   - **Resolve tool definition**: Look up `ToolDefinition` by name. If not in registry → reject (do not execute).
   - **Allowlist check**: Verify that the current agent’s `allowed_tool_names` includes this tool. If not → reject.
   - **Schema validation**: Validate `arguments` against the tool’s JSON Schema. If invalid → reject and optionally return error to LLM for retry.
   - **Approval policy**: If `ToolDefinition.requires_approval` is true:
     - Create `ApprovalRequest` (workflow_run_id, step_id, tool_name, sanitized arguments).
     - Set run (or step) to `awaiting_approval`.
     - Return to application "approval_required"; application pauses step.
   - **Log**: Log tool request (run_id, step_id, tool_name, args hash or truncated).
   - **Execute**: Call the registered executor for this tool with validated arguments (and run context if needed).
   - **Log result**: Log success/failure and result (truncated if large).
   - **Return** `ToolCallResult` to application.
4. Application passes result back into the next LLM call or step output.

## Components

- **ToolExecutionGatewayInterface** (Application/Domain): `execute(ToolCallRequest): ToolCallResult` or `requestExecution(...): ToolCallResult | ApprovalRequired`.
- **ToolRegistry**: In-memory or config-driven registry of `ToolDefinition` (name, schema, requires_approval, description). Loaded from config/tools.
- **Tool Executors**: One class per tool (e.g. `RetrieveChunksExecutor`). Registry maps tool name → executor. Executors receive validated args and run context; return structured result.
- **Approval integration**: Gateway calls ApprovalService to create requests and check status; ApprovalService is used by API/UI to resolve (approve/reject). On approve, gateway is invoked again to perform execution.

## Security

- **Document and user content** do not influence: which tools exist, allowlist per agent, schema, or approval policy. Those come from config and agent profile.
- **Input validation**: Only validated arguments are passed to executors; executors must not trust raw LLM output.
- **Sensitive tools**: Require human approval; arguments are sanitized before showing in approval UI (e.g. mask secrets).

## V1 Tools (Example)

- **retrieve_chunks**: Input: `query` (string), `top_k` (int, default 10). Returns chunks + scores. Used by analyst. Does not require approval.
- (Future: **send_notification**, **export_report** — can set `requires_approval = true`.)

## Logging

- Every tool request: run_id, step_id, tool_name, timestamp, approval_required (bool).
- Every tool execution: run_id, step_id, tool_name, success, result_size or error, executed_at.
- Approval events: requested, approved/rejected, resolved_by, resolved_at.
