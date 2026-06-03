<?php

namespace App\Services;

use App\Models\AcademicCourse;
use App\Models\AcademicRecord;
use App\Models\Document;
use App\Models\OcrResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function upload(UploadedFile $file, int $userId): Document
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('documents', $filename, 'local');

        return Document::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'status' => 'pending',
            'uploaded_by' => $userId,
        ]);
    }

    public function saveOcrResult(Document $document, array $ocrData): OcrResult
    {
        return OcrResult::create([
            'document_id' => $document->id,
            'raw_text' => $ocrData['raw_text'] ?? '',
            'confidence_score' => $ocrData['confidence_score'] ?? 0,
            'processing_time' => $ocrData['processing_time'] ?? null,
        ]);
    }

    public function saveAcademicRecord(Document $document, array $fields): AcademicRecord
    {
        return AcademicRecord::create([
            'document_id' => $document->id,
            'student_name' => $fields['student_name'] ?? null,
            'student_number' => $fields['student_number'] ?? null,
            'nik' => $fields['nik'] ?? null,
            'university' => $fields['university'] ?? null,
            'faculty' => $fields['faculty'] ?? null,
            'study_program' => $fields['study_program'] ?? null,
            'gelar' => $fields['gelar'] ?? null,
            'gpa' => $fields['gpa'] ?? null,
            'total_sks' => $fields['total_sks'] ?? null,
            'predikat_kelulusan' => $fields['predikat_kelulusan'] ?? null,
            'graduation_year' => $fields['graduation_year'] ?? null,
            'tanggal_lulus' => $fields['tanggal_lulus'] ?? null,
            'nomor_transkrip' => $fields['nomor_transkrip'] ?? null,
        ]);
    }

    public function updateAcademicRecord(Document $document, array $fields): AcademicRecord
    {
        $record = $document->academicRecord;

        if (!$record) {
            return $this->saveAcademicRecord($document, $fields);
        }

        $record->update([
            'student_name' => $fields['student_name'] ?? $record->student_name,
            'student_number' => $fields['student_number'] ?? $record->student_number,
            'nik' => $fields['nik'] ?? $record->nik,
            'university' => $fields['university'] ?? $record->university,
            'faculty' => $fields['faculty'] ?? $record->faculty,
            'study_program' => $fields['study_program'] ?? $record->study_program,
            'gelar' => $fields['gelar'] ?? $record->gelar,
            'gpa' => $fields['gpa'] ?? $record->gpa,
            'total_sks' => $fields['total_sks'] ?? $record->total_sks,
            'predikat_kelulusan' => $fields['predikat_kelulusan'] ?? $record->predikat_kelulusan,
            'graduation_year' => $fields['graduation_year'] ?? $record->graduation_year,
            'tanggal_lulus' => $fields['tanggal_lulus'] ?? $record->tanggal_lulus,
            'nomor_transkrip' => $fields['nomor_transkrip'] ?? $record->nomor_transkrip,
        ]);

        return $record->fresh();
    }

    public function saveAcademicCourses(AcademicRecord $academicRecord, array $courses): void
    {
        $academicRecord->academicCourses()->delete();

        foreach ($courses as $course) {
            if (!empty($course['nama_mata_kuliah'])) {
                AcademicCourse::create([
                    'academic_record_id' => $academicRecord->id,
                    'nama_mata_kuliah' => $course['nama_mata_kuliah'],
                    'nilai' => $course['nilai'] ?? null,
                    'sks' => $course['sks'] ?? null,
                ]);
            }
        }
    }

    public function extractAndSaveAcademicData(Document $document, AcademicParserService $parser): AcademicRecord
    {
        $ocrResult = $document->ocrResult;

        if (!$ocrResult || empty($ocrResult->raw_text)) {
            throw new \RuntimeException('No OCR result available for extraction.');
        }

        $parsed = $parser->parse($ocrResult->raw_text);

        $record = $this->updateAcademicRecord($document, $parsed['fields']);

        $this->saveAcademicCourses($record, $parsed['courses']);

        return $record->fresh()->load('academicCourses');
    }
}
