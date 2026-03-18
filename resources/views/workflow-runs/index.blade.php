<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow History - AgentLoom</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; margin: 24px; background: #f9fafb; color: #111827; }
        .container { max-width: 1100px; margin: 0 auto; }
        .summary { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .stat { flex: 1 1 180px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px; }
        .stat .label { color: #6b7280; font-size: 13px; }
        .stat .value { font-size: 22px; font-weight: 800; margin-top: 6px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #f3f4f6; font-size: 14px; vertical-align: top; }
        th { color: #6b7280; font-weight: 700; font-size: 13px; }
        .filters { display: flex; gap: 10px; align-items: center; margin-bottom: 12px; }
        select, a.btn { padding: 8px 10px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; }
        a.link { color: #2563eb; text-decoration: none; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
<div class="container">
    <h1 style="margin:0 0 8px;">Workflow History</h1>
    <div class="muted" style="margin-bottom: 18px;">
        Runs are executed step-by-step with persisted state. Use the filter to focus on a specific status.
    </div>

    <div class="summary">
        @php
            $total = $runs->count();
            $completed = (int)($summary['completed'] ?? 0);
            $failed = (int)($summary['failed'] ?? 0);
            $awaiting = (int)($summary['awaiting_approval'] ?? 0);
            $running = (int)($summary['running'] ?? 0);
        @endphp
        <div class="stat">
            <div class="label">Total runs (shown)</div>
            <div class="value">{{ $total }}</div>
        </div>
        <div class="stat">
            <div class="label">Completed</div>
            <div class="value">{{ $completed }}</div>
        </div>
        <div class="stat">
            <div class="label">Running</div>
            <div class="value">{{ $running }}</div>
        </div>
        <div class="stat">
            <div class="label">Awaiting approval</div>
            <div class="value">{{ $awaiting }}</div>
        </div>
        <div class="stat">
            <div class="label">Failed</div>
            <div class="value">{{ $failed }}</div>
        </div>
    </div>

    <div class="card">
        <form class="filters" method="GET" action="{{ route('workflow-runs.index') }}">
            <div>
                <label class="muted" style="font-size:13px;">Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="" @selected(empty($filterStatus))>All</option>
                    @foreach (['pending','running','completed','failed','awaiting_approval','retry_scheduled'] as $s)
                        <option value="{{ $s }}" @selected(($filterStatus ?? '') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <a class="link" href="{{ route('documents.upload') }}">Upload documents</a>
            </div>
        </form>

        <table>
            <thead>
            <tr>
                <th>Run</th>
                <th>Workflow</th>
                <th>Status</th>
                <th>Started</th>
                <th>Updated</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($runs as $run)
                <tr>
                    <td>
                        <a class="link" href="{{ route('workflow-runs.show', $run->id) }}">{{ $run->id }}</a>
                    </td>
                    <td>{{ $run->workflow_id }}</td>
                    <td><strong>{{ $run->status }}</strong></td>
                    <td>{{ optional($run->started_at)->toDateTimeString() }}</td>
                    <td>{{ $run->updated_at?->toDateTimeString() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No workflow runs yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

