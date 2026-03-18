<?php

namespace App\Http\Controllers;

use App\Jobs\IngestUploadedDocument;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function showUploadForm()
    {
        return view('documents.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'documents.*' => ['required', 'file', 'max:20000'], // 20MB per file (adjust later)
        ]);

        $files = $request->file('documents', []);
        if (!is_array($files) || count($files) === 0) {
            return back()->withErrors(['documents' => 'No files provided.'])->withInput();
        }

        $disk = Storage::disk('local');

        $results = [];

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName() ?: 'document';
            $mime = (string) $file->getClientMimeType();

            $path = $disk->putFileAs(
                'documents/' . now()->format('Ymd'),
                $file,
                Str::random(12) . '_' . $file->getClientOriginalName()
            );

            $document = Document::query()->create([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'mime_type' => $mime,
                'storage_path' => $path,
                'status' => 'uploaded',
                'meta' => [],
            ]);

            // Speed improvement: ingest asynchronously; UI returns immediately.
            try {
                IngestUploadedDocument::dispatch($document->id);
            } catch (\Throwable $e) {
                // If queue is down, do the ingest synchronously to avoid "stuck" uploads.
                IngestUploadedDocument::dispatchSync($document->id);
            }

            $results[] = [
                'document_id' => $document->id,
                'status' => $document->status,
            ];
        }

        return redirect()
            ->route('documents.upload')
            ->with('message', 'Upload complete. Ingestion queued for ' . count($results) . ' document(s).');
    }
}

