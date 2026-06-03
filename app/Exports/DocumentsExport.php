<?php

namespace App\Exports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DocumentsExport implements FromQuery, WithHeadings, WithMapping
{
    protected ?string $qualifiedFilter;

    public function __construct(?string $qualifiedFilter = null)
    {
        $this->qualifiedFilter = $qualifiedFilter;
    }

    public function query()
    {
        $query = Document::with(['ocrResult', 'academicRecord', 'uploader']);

        if ($this->qualifiedFilter === 'qualified') {
            $query->whereHas('academicRecord', fn($q) => $q->whereNotNull('qualified_at'));
        } elseif ($this->qualifiedFilter === 'unqualified') {
            $query->whereHas('academicRecord', fn($q) => $q->whereNull('qualified_at'));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Filename',
            'Type',
            'Status',
            'Student Name',
            'Student Number',
            'University',
            'Faculty',
            'Study Program',
            'GPA',
            'Graduation Year',
            'Confidence Score',
            'Uploaded By',
            'Uploaded At',
        ];
    }

    public function map($document): array
    {
        return [
            $document->id,
            $document->original_filename,
            $document->file_type,
            $document->status,
            $document->academicRecord?->student_name ?? '-',
            $document->academicRecord?->student_number ?? '-',
            $document->academicRecord?->university ?? '-',
            $document->academicRecord?->faculty ?? '-',
            $document->academicRecord?->study_program ?? '-',
            $document->academicRecord?->gpa ?? '-',
            $document->academicRecord?->graduation_year ?? '-',
            $document->ocrResult?->confidence_score ?? '-',
            $document->uploader?->name ?? '-',
            $document->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
