@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Upload Document</h1>
        <p class="mt-1 text-sm text-text-secondary">Upload academic documents for OCR processing</p>
    </div>

    <div class="mx-auto max-w-2xl"
         x-data="{
             step: 'select',
             file: null,
             progress: 0,
             document: null,
             error: null,
             csrf: '{{ csrf_token() }}',

             handleFileSelect(event) {
                 this.file = event.detail.file;
                 this.upload();
             },

             async upload() {
                 this.step = 'uploading';
                 this.progress = 0;
                 this.error = null;

                 const formData = new FormData();
                 formData.append('file', this.file);

                 try {
                     const response = await axios.post('{{ route('documents.store') }}', formData, {
                         headers: {
                             'Content-Type': 'multipart/form-data',
                             'X-CSRF-TOKEN': this.csrf,
                         },
                         onUploadProgress: (e) => {
                             this.progress = e.total
                                 ? Math.round((e.loaded * 100) / e.total)
                                 : 0;
                         },
                     });

                     this.document = response.data.document;
                     this.step = 'uploaded';
                 } catch (err) {
                     this.error = err.response?.data?.message
                         || err.response?.data?.errors?.file?.[0]
                         || 'Upload failed. Please try again.';
                     this.step = 'error';
                 }
             },

             async processOCR() {
                 this.step = 'processing';
                 this.error = null;

                 try {
                    await axios.post('{{ route('documents.ocr', ['document' => '__id__']) }}'.replace('__id__', this.document.id), {}, {
                        headers: { 'X-CSRF-TOKEN': this.csrf },
                        timeout: 30000,
                    });
                     this.step = 'done';
                 } catch (err) {
                     this.error = err.response?.data?.message || 'OCR processing failed.';
                     this.step = 'error';
                 }
             },

             reset() {
                 this.step = 'select';
                 this.file = null;
                 this.progress = 0;
                 this.document = null;
                 this.error = null;
             },
         }"
         @file-selected="handleFileSelect">

        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
            {{-- Step: Select File --}}
            <template x-if="step === 'select'">
                <div>
                    <x-upload-area />
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <a href="{{ route('documents.index') }}" class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                            Cancel
                        </a>
                    </div>
                </div>
            </template>

            {{-- Step: Uploading with Progress --}}
            <template x-if="step === 'uploading'">
                <div class="text-center py-8">
                    <svg class="mx-auto mb-4 h-12 w-12 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-text-primary mb-2">Uploading document...</p>
                    <div class="mx-auto w-full max-w-sm rounded-full bg-gray-200 h-2.5">
                        <div class="h-2.5 rounded-full bg-primary transition-all duration-300"
                             :style="'width: ' + progress + '%'"></div>
                    </div>
                    <p class="mt-2 text-xs text-text-secondary" x-text="progress + '%'"></p>
                </div>
            </template>

            {{-- Step: Uploaded — Show Process OCR & View buttons --}}
            <template x-if="step === 'uploaded'">
                <div class="text-center py-8">
                    <svg class="mx-auto mb-4 h-12 w-12 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-semibold text-text-primary mb-1">Upload Successful</p>
                    <p class="text-sm text-text-secondary mb-6" x-text="'Ready to process: ' + document.original_filename"></p>
                    <div class="flex items-center justify-center gap-3">
                        <a :href="'/documents/' + document.id"
                           class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                            View Document
                        </a>
                        <button @click="processOCR"
                                class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                            Process OCR
                        </button>
                    </div>
                </div>
            </template>

            {{-- Step: OCR Processing --}}
            <template x-if="step === 'processing'">
                <div class="text-center py-8">
                    <svg class="mx-auto mb-4 h-12 w-12 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-text-primary mb-1">Processing OCR...</p>
                    <p class="text-xs text-text-secondary">Please wait while we extract text from the document.</p>
                </div>
            </template>

            {{-- Step: Done — Redirect to document page --}}
            <template x-if="step === 'done'">
                <div class="text-center py-8">
                    <svg class="mx-auto mb-4 h-12 w-12 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-semibold text-text-primary mb-1">OCR Processing Started</p>
                    <p class="text-sm text-text-secondary mb-6">The document is being processed in the background.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('documents.index') }}"
                           class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                            Back to Documents
                        </a>
                        <a :href="'/documents/' + document.id"
                           class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                            View Results
                        </a>
                    </div>
                </div>
            </template>

            {{-- Step: Error --}}
            <template x-if="step === 'error'">
                <div class="text-center py-8">
                    <svg class="mx-auto mb-4 h-12 w-12 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-lg font-semibold text-text-primary mb-1">Something went wrong</p>
                    <p class="text-sm text-danger mb-6" x-text="error"></p>
                    <button @click="reset"
                            class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                        Try Again
                    </button>
                </div>
            </template>
        </div>
    </div>
@endsection