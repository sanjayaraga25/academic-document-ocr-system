<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'status',
        'failed_reason',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function ocrResult()
    {
        return $this->hasOne(OcrResult::class);
    }

    public function academicRecord()
    {
        return $this->hasOne(AcademicRecord::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }
}
