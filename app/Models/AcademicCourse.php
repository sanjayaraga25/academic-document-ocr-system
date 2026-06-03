<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicCourse extends Model
{
    protected $fillable = [
        'academic_record_id',
        'nama_mata_kuliah',
        'nilai',
        'sks',
    ];

    public function academicRecord()
    {
        return $this->belongsTo(AcademicRecord::class);
    }
}
