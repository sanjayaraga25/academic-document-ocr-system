<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcrResult extends Model
{
    protected $fillable = [
        'document_id',
        'raw_text',
        'confidence_score',
        'processing_time',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function academicRecord()
    {
        return $this->hasOne(AcademicRecord::class, 'document_id', 'document_id');
    }
}
