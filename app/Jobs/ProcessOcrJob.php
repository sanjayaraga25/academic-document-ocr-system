<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\OcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOcrJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle(OcrService $ocrService, DocumentService $documentService): void
    {
        try {
            $result = $ocrService->process($this->document->file_path, $this->document->original_filename);

            $documentService->saveOcrResult($this->document, $result);

            if (isset($result['fields'])) {
                $documentService->saveAcademicRecord($this->document, $result['fields']);
            }

            $this->document->update(['status' => 'ready', 'failed_reason' => null]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('OCR Processing failed for document #' . $this->document->id . ': ' . $message);
            $this->document->update([
                'status' => 'error',
                'failed_reason' => $message,
            ]);
        }
    }
}
