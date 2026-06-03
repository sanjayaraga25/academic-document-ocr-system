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
        Schema::create('academic_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_record_id')->constrained()->cascadeOnDelete();
            $table->string('nama_mata_kuliah');
            $table->string('nilai')->nullable();
            $table->unsignedTinyInteger('sks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_courses');
    }
};
