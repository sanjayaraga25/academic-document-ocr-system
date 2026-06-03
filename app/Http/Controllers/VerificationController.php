<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyDocumentRequest;
use App\Models\Document;
use App\Models\Verification;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['uploader', 'ocrResult'])
            ->whereIn('status', ['pending', 'ready']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $documents = $query->latest()->paginate(15);

        return view('pages.verifications.index', compact('documents'));
    }

    public function verify(Document $document, VerifyDocumentRequest $request)
    {
        if (in_array($document->status, ['verified', 'rejected'])) {
            return back()->with('error', 'Document has already been verified.');
        }

        Verification::create([
            'document_id' => $document->id,
            'verifier_id' => auth()->id(),
            'status' => $request->status,
            'notes' => $request->notes,
            'verified_at' => now(),
        ]);

        $document->update(['status' => $request->status]);

        return back()->with('success', 'Document ' . $request->status . ' successfully.');
    }
}
