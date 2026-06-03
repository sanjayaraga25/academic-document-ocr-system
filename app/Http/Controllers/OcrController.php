<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOcrJob;
use App\Models\Document;
use App\Services\OcrService;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function process(Document $document, Request $request)
    {
        if ($document->status === 'verified') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Document already verified.'], 422);
            }
            return back()->with('error', 'Document already verified.');
        }

        $document->update(['status' => 'processing']);

        ProcessOcrJob::dispatch($document);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'OCR processing started.',
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'OCR processing started.');
    }

    public function reprocess(Document $document, Request $request)
    {
        if ($document->status === 'verified') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Cannot reprocess a verified document.'], 422);
            }
            return back()->with('error', 'Cannot reprocess a verified document.');
        }

        $document->update(['status' => 'processing', 'failed_reason' => null]);

        ProcessOcrJob::dispatch($document);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'OCR reprocessing started.',
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'OCR reprocessing started.');
    }

    public function health(OcrService $ocrService)
    {
        $isHealthy = $ocrService->health();

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
        ]);
    }
}
