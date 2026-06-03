<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('student_number');
            $table->string('gelar')->nullable()->after('study_program');
            $table->date('tanggal_lulus')->nullable()->after('graduation_year');
            $table->decimal('total_sks', 5, 1)->nullable()->after('gpa');
            $table->string('predikat_kelulusan')->nullable()->after('total_sks');
            $table->string('nomor_transkrip')->nullable()->after('predikat_kelulusan');
        });
    }

    public function down(): void
    {
        Schema::table('academic_records', function (Blueprint $table) {
            $table->dropColumn(['nik', 'gelar', 'tanggal_lulus', 'total_sks', 'predikat_kelulusan', 'nomor_transkrip']);
        });
    }
};
