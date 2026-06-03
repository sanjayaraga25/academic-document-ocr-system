<?php

namespace App\Http\Controllers;

use App\Exports\DocumentsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        return Excel::download(new DocumentsExport($request->qualified), 'documents.xlsx');
    }

    public function csv(Request $request)
    {
        return Excel::download(new DocumentsExport($request->qualified), 'documents.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function pdf(Request $request)
    {
        $documents = \App\Models\Document::with(['ocrResult', 'academicRecord', 'uploader'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->qualified, function ($q, $v) {
                if ($v === 'qualified') {
                    $q->whereHas('academicRecord', fn($q) => $q->whereNotNull('qualified_at'));
                } elseif ($v === 'unqualified') {
                    $q->whereHas('academicRecord', fn($q) => $q->whereNull('qualified_at'));
                }
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.documents-pdf', compact('documents'));
        return $pdf->download('documents.pdf');
    }
}
