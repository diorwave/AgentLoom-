<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IngestUploadedDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function handle(): void
    {
        /** @var Document $document */
        $document = Document::query()->findOrFail($this->documentId);

        $document->update(['status' => 'parsing']);

        $disk = Storage::disk('local');
        $path = $document->storage_path;

        if (!$disk->exists($path)) {
            $document->update(['status' => 'failed', 'meta' => array_merge((array) $document->meta, ['error' => 'missing_file'])]);
            return;
        }

        $mime = strtolower((string) $document->mime_type);

        try {
            $raw = $disk->get($path);
            $text = match (true) {
                str_contains($mime, 'text/plain') => $raw,
                str_contains($mime, 'text/html') => strip_tags($raw),
                // Keep V1 parser support minimal in scaffold: fail fast for unsupported formats.
                default => throw new \RuntimeException('unsupported_mime_type_' . $mime),
            };
        } catch (\Throwable $e) {
            $document->update(['status' => 'failed', 'meta' => array_merge((array) $document->meta, ['error' => $e->getMessage()])]);
            return;
        }

        // Normalize without attempting to interpret content.
        $normalized = preg_replace('/\\s+/u', ' ', (string) $text);
        $normalized = trim((string) $normalized);

        $document->update(['status' => 'chunking']);

        $chunkSize = 1500;
        $overlap = 200;
        $chunks = [];

        $len = strlen($normalized);
        $start = 0;
        $position = 0;

        while ($start < $len) {
            $end = min($len, $start + $chunkSize);
            $chunkText = substr($normalized, $start, $end - $start);
            if (trim($chunkText) !== '') {
                $chunks[] = [
                    'id' => Str::ulid()->toRfc4122(),
                    'document_id' => $document->id,
                    'content' => $chunkText,
                    'position' => $position++,
                    'token_count' => null,
                    'meta' => [
                        'start_char' => $start,
                        'end_char' => $end,
                    ],
                ];
            }

            if ($end === $len) {
                break;
            }
            $start = max(0, $end - $overlap);
        }

        DocumentChunk::query()->where('document_id', $document->id)->delete();
        foreach ($chunks as $chunk) {
            DocumentChunk::query()->create($chunk);
        }

        $document->update([
            'status' => 'ready',
            'meta' => array_merge((array) $document->meta, [
                'chunk_count' => count($chunks),
                'ingested_at' => now()->toIso8601String(),
            ]),
        ]);
    }
}

