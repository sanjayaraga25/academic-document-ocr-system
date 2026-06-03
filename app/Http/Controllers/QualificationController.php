<?php

namespace App\Http\Controllers;

use App\Models\AcademicRecord;
use App\Models\Document;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicRecord::with('document.uploader')
            ->whereHas('document');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'qualified') {
                $query->qualified();
            } elseif ($status === 'unqualified') {
                $query->unqualified();
            }
        }

        if ($minGpa = $request->get('min_gpa')) {
            $query->where('gpa', '>=', $minGpa);
        }

        if ($prodi = $request->get('study_program')) {
            $query->where('study_program', 'like', "%{$prodi}%");
        }

        $records = $query->latest()->paginate(20);

        $studyPrograms = AcademicRecord::distinct()
            ->whereNotNull('study_program')
            ->orderBy('study_program')
            ->pluck('study_program');

        $stats = [
            'total' => AcademicRecord::count(),
            'qualified' => AcademicRecord::qualified()->count(),
            'unqualified' => AcademicRecord::unqualified()->count(),
        ];

        return view('pages.qualification.index', compact('records', 'studyPrograms', 'stats'));
    }

    public function qualify(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:academic_records,id',
        ]);

        $count = AcademicRecord::whereIn('id', $request->ids)
            ->whereNull('qualified_at')
            ->update(['qualified_at' => now()]);

        return back()->with('success', "{$count} records qualified successfully.");
    }

    public function unqualify(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:academic_records,id',
        ]);

        $count = AcademicRecord::whereIn('id', $request->ids)
            ->whereNotNull('qualified_at')
            ->update(['qualified_at' => null]);

        return back()->with('success', "{$count} records removed from qualified.");
    }
}
