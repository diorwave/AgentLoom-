<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// V1 workflow API placeholders — to be implemented in Phase 2+
// Route::post('/workflow-runs', [WorkflowRunController::class, 'store']);
// Route::get('/workflow-runs/{id}', [WorkflowRunController::class, 'show']);
// Route::post('/documents/upload', [DocumentController::class, 'upload']);
// Route::post('/approvals/{id}/approve', [ApprovalController::class, 'approve']);
// Route::post('/approvals/{id}/reject', [ApprovalController::class, 'reject']);
