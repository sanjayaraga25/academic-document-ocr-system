<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

    Route::get('/documents/{document}/status', [DocumentController::class, 'status'])->name('documents.status');
    Route::post('/documents/{document}/ocr', [OcrController::class, 'process'])->name('documents.ocr');
    Route::post('/documents/{document}/reprocess', [OcrController::class, 'reprocess'])->name('documents.reprocess');
    Route::post('/documents/{document}/extract-academic', [DocumentController::class, 'extract'])->name('documents.extract-academic');
    Route::post('/documents/{document}/re-extract-academic', [DocumentController::class, 'reExtract'])->name('documents.re-extract-academic');

    Route::get('/verifications', [VerificationController::class, 'index'])->name('verifications.index');
    Route::post('/documents/{document}/verify', [VerificationController::class, 'verify'])->name('documents.verify');

    Route::get('/qualification', [QualificationController::class, 'index'])->name('qualification.index');
    Route::post('/qualification/qualify', [QualificationController::class, 'qualify'])->name('qualification.qualify');
    Route::post('/qualification/unqualify', [QualificationController::class, 'unqualify'])->name('qualification.unqualify');

    Route::get('/ocr/health', [OcrController::class, 'health'])->name('ocr.health');

    Route::get('/export/excel', [ExportController::class, 'excel'])->name('export.excel');
    Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
    Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
});

require __DIR__.'/auth.php';
