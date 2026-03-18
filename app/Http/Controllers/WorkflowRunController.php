<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRun;
use Illuminate\Http\Request;

class WorkflowRunController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = WorkflowRun::query()->orderByDesc('created_at');
        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        $runs = $query->limit(50)->get();

        $summary = WorkflowRun::query()
            ->selectRaw('status, count(*) as cnt')
            ->whereIn('id', $runs->pluck('id'))
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('workflow-runs.index', [
            'runs' => $runs,
            'summary' => $summary,
            'filterStatus' => $status,
        ]);
    }

    public function show(string $id)
    {
        $run = WorkflowRun::query()->findOrFail($id);

        $steps = $run->steps()
            ->orderBy('order')
            ->get();

        $summarizeStep = $steps->firstWhere('step_key', 'summarize');
        $analyseStep = $steps->firstWhere('step_key', 'analyse');

        $report = null;
        if ($summarizeStep?->output_payload) {
            $payload = $summarizeStep->output_payload;
            $report = $payload['report'] ?? $payload['final_report'] ?? $payload['content'] ?? null;
        }

        $sources = [];
        if ($analyseStep?->output_payload) {
            $payload = $analyseStep->output_payload;
            $refs = $payload['evidence_references'] ?? $payload['sources'] ?? [];
            foreach ((array) $refs as $ref) {
                $sources[] = [
                    'document_id' => $ref['document_id'] ?? null,
                    'document_name' => $ref['document_name'] ?? null,
                    'chunk_id' => $ref['chunk_id'] ?? null,
                    'excerpt' => $ref['excerpt'] ?? null,
                    'score' => $ref['score'] ?? null,
                ];
            }
        }

        return view('workflow-runs.show', [
            'run' => $run,
            'steps' => $steps,
            'report' => $report,
            'sources' => $sources,
        ]);
    }
}

