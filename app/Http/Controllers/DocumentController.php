<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentRequest;
use App\Models\Document;
use App\Services\AcademicParserService;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['uploader', 'ocrResult', 'academicRecord']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('academicRecord', function ($q) use ($search) {
                      $q->where('student_name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $documents = $query->latest()->paginate(15);

        return view('pages.documents.index', compact('documents'));
    }

    public function show(Document $document)
    {
        $document->load(['uploader', 'ocrResult', 'academicRecord', 'verifications.verifier']);
        return view('pages.documents.show', compact('document'));
    }

    public function status(Document $document): JsonResponse
    {
        return response()->json([
            'id' => $document->id,
            'status' => $document->status,
            'failed_reason' => $document->failed_reason,
        ]);
    }

    public function extract(Document $document, DocumentService $documentService, AcademicParserService $parser, Request $request)
    {
        if ($document->status !== 'ready') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'OCR must be completed first.'], 422);
            }
            return back()->with('error', 'OCR must be completed first.');
        }

        if (!$document->ocrResult) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'No OCR result found.'], 422);
            }
            return back()->with('error', 'No OCR result found.');
        }

        try {
            $record = $documentService->extractAndSaveAcademicData($document, $parser);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Academic data extracted successfully.',
                    'data' => $record,
                ]);
            }

            return back()->with('success', 'Academic data extracted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function reExtract(Document $document, DocumentService $documentService, AcademicParserService $parser, Request $request)
    {
        if ($document->status !== 'ready') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'OCR must be completed first.'], 422);
            }
            return back()->with('error', 'OCR must be completed first.');
        }

        if (!$document->ocrResult) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'No OCR result found.'], 422);
            }
            return back()->with('error', 'No OCR result found.');
        }

        try {
            if ($document->academicRecord) {
                $document->academicRecord->academicCourses()->delete();
                $document->academicRecord->delete();
                $document->unsetRelation('academicRecord');
            }

            $record = $documentService->extractAndSaveAcademicData($document, $parser);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Academic data re-extracted successfully.',
                    'data' => $record,
                ]);
            }

            return back()->with('success', 'Academic data re-extracted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function upload()
    {
        return view('pages.documents.upload');
    }

    public function store(UploadDocumentRequest $request, DocumentService $documentService)
    {
        $document = $documentService->upload($request->file('file'), auth()->id());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Document uploaded successfully.',
                'document' => $document->load('uploader'),
            ]);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document uploaded successfully.');
    }
}
