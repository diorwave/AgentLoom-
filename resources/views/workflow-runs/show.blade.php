<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Run - AgentLoom</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; margin: 24px; background: #f9fafb; color: #111827; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; margin-bottom: 16px; }
        h1 { margin:0 0 10px; }
        .meta { color: #6b7280; font-size: 14px; }
        .steps { display: grid; grid-template-columns: 1fr; gap: 10px; }
        .step { border: 1px solid #f3f4f6; border-radius: 12px; padding: 12px; }
        .step .key { font-weight: 800; }
        .sourcesBox {
            border: 2px solid #7c3aed;
            background: #faf5ff;
            border-radius: 14px;
            padding: 14px;
        }
        .sourceRow {
            padding: 10px 10px;
            border-top: 1px solid #eadcff;
        }
        .sourceRow:first-child { border-top: 0; }
        .pill { display:inline-block; padding: 2px 8px; border-radius: 999px; background:#ede9fe; color:#5b21b6; font-size:12px; font-weight:700; }
        .btnLink { color:#2563eb; text-decoration:none; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Workflow Run</h1>
    <div class="meta">
        <div><a class="btnLink" href="{{ route('workflow-runs.index') }}">Back to history</a></div>
        <div style="margin-top:8px;">
            <span class="pill">{{ $run->status }}</span>
            <div style="margin-top:6px;">Workflow: {{ $run->workflow_id }}</div>
            <div>Started: {{ optional($run->started_at)->toDateTimeString() }}</div>
            <div>Updated: {{ $run->updated_at?->toDateTimeString() }}</div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin:0 0 10px;">Steps</h2>
        <div class="steps">
            @foreach ($steps as $step)
                <div class="step">
                    <div class="key">{{ $step->step_key }}</div>
                    <div class="meta">
                        Status: <strong>{{ $step->status }}</strong> · Order: {{ $step->order }}
                        @if (!empty($step->requires_approval)) · Approval required
                        @endif
                    </div>
                    @if ($step->completed_at)
                        <div class="meta">Completed: {{ $step->completed_at->toDateTimeString() }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2 style="margin:0 0 10px;">Final Report</h2>
        @if ($report)
            <pre>{{ is_string($report) ? $report : json_encode($report, JSON_PRETTY_PRINT) }}</pre>
        @else
            <div class="meta">No final report output is stored yet for this run.</div>
        @endif
    </div>

    <div class="sourcesBox">
        <h2 style="margin:0 0 10px;">Sources</h2>
        @if (count($sources) > 0)
            <div class="meta" style="margin-bottom: 12px;">
                Evidence references used for the report. Each source includes the originating document and chunk excerpt.
            </div>
            @foreach ($sources as $src)
                <div class="sourceRow">
                    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:baseline;">
                        <div style="font-weight:800;">
                            {{ $src['document_name'] ?: 'Document ' . ($src['document_id'] ?: '') }}
                        </div>
                        <div class="meta">
                            Chunk: {{ $src['chunk_id'] ?: 'N/A' }}
                        </div>
                        @if (!is_null($src['score']))
                            <div class="meta">Score: {{ $src['score'] }}</div>
                        @endif
                    </div>
                    @if (!empty($src['excerpt']))
                        <div style="margin-top:8px;">
                            <pre>{{ $src['excerpt'] }}</pre>
                        </div>
                    @else
                        <div class="meta" style="margin-top:8px;">No excerpt stored for this reference.</div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="meta">No sources references were found in the stored step outputs yet.</div>
        @endif
    </div>
</div>
</body>
</html>

