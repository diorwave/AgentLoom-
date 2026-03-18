<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowRunController;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/workflow-runs', [WorkflowRunController::class, 'index'])->name('workflow-runs.index');
Route::get('/workflow-runs/{id}', [WorkflowRunController::class, 'show'])->name('workflow-runs.show');

Route::get('/documents/upload', [DocumentController::class, 'showUploadForm'])->name('documents.upload');
Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload.post');
