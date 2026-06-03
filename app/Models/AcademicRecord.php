<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicRecord extends Model
{
    protected $fillable = [
        'document_id',
        'student_name',
        'student_number',
        'nik',
        'university',
        'faculty',
        'study_program',
        'gelar',
        'gpa',
        'total_sks',
        'predikat_kelulusan',
        'graduation_year',
        'tanggal_lulus',
        'nomor_transkrip',
        'qualified_at',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'total_sks' => 'decimal:1',
            'graduation_year' => 'integer',
            'tanggal_lulus' => 'date:Y-m-d',
            'qualified_at' => 'datetime',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function academicCourses()
    {
        return $this->hasMany(AcademicCourse::class);
    }

    public function scopeQualified($query)
    {
        return $query->whereNotNull('qualified_at');
    }

    public function scopeUnqualified($query)
    {
        return $query->whereNull('qualified_at');
    }

    public function qualify(): static
    {
        $this->update(['qualified_at' => now()]);
        return $this;
    }

    public function unqualify(): static
    {
        $this->update(['qualified_at' => null]);
        return $this;
    }

    public function getIsQualifiedAttribute(): bool
    {
        return $this->qualified_at !== null;
    }
}
