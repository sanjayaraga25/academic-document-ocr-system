@extends('layouts.app')

@section('title', 'Document #' . $document->id)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('documents.index') }}" class="text-sm text-text-secondary hover:text-text-primary">Documents</a>
                <svg class="h-4 w-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-sm font-medium text-text-primary">#{{ $document->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-text-primary">{{ $document->original_filename }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$document->status" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3"
         x-data="{
             status: '{{ $document->status }}',
             failedReason: {{ $document->failed_reason ? json_encode($document->failed_reason) : 'null' }},
             polling: null,
             retryCount: 0,
             maxRetries: 120,
             timedOut: false,
             hasOcrResult: {{ $document->ocrResult ? 'true' : 'false' }},

             init() {
                 if (this.status === 'processing') {
                     this.startPolling();
                 }
             },

             startPolling() {
                 this.polling = setInterval(() => {
                     if (this.retryCount >= this.maxRetries) {
                         this.stopPolling();
                         this.timedOut = true;
                         return;
                     }
                     this.retryCount++;
                     fetch('{{ route('documents.status', $document) }}')
                         .then(r => r.json())
                         .then(data => {
                             this.status = data.status;
                             this.failedReason = data.failed_reason;
                             if (data.status !== 'processing') {
                                 this.stopPolling();
                                 if (data.status === 'ready') {
                                     window.location.reload();
                                 }
                             }
                         })
                         .catch(() => {
                             if (this.retryCount >= this.maxRetries) {
                                 this.stopPolling();
                                 this.timedOut = true;
                             }
                         });
                 }, 5000);
             },

             stopPolling() {
                 if (this.polling) {
                     clearInterval(this.polling);
                     this.polling = null;
                 }
             },

             destroy() {
                 this.stopPolling();
             }
         }">

        <div class="lg:col-span-2 space-y-6">
            {{-- Queue health warning --}}
            <template x-if="status === 'processing'">
                <div class="rounded-xl border border-warning bg-warning-light bg-opacity-50 p-4 shadow-sm flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-text-primary">OCR sedang diproses...</p>
                        <p class="text-xs text-text-secondary mt-1">Status akan diperbarui otomatis. Halaman akan reload setelah selesai.</p>
                    </div>
                </div>
            </template>

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">Document Info</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-text-secondary">File Name</p>
                        <p class="text-sm font-medium text-text-primary">{{ $document->original_filename }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">File Type</p>
                        <p class="text-sm font-medium text-text-primary uppercase">{{ $document->file_type }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Uploaded By</p>
                        <p class="text-sm font-medium text-text-primary">{{ $document->uploader?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-text-secondary">Uploaded At</p>
                        <p class="text-sm font-medium text-text-primary">{{ $document->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                        Back to List
                    </a>
                    @if($document->status === 'processing')
                        <span class="inline-flex items-center gap-1 rounded-lg bg-warning-light px-3 py-1.5 text-sm font-medium text-warning">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing...
                        </span>
                    @elseif($document->status === 'ready' || $document->status === 'error')
                        <form action="{{ route('documents.reprocess', $document) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                                Reprocess OCR
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- OCR Error with reason --}}
            @if($document->status === 'error')
                <div class="rounded-xl border border-danger bg-danger-light bg-opacity-50 p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-6 w-6 shrink-0 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-danger">OCR Failed</p>
                            @if($document->failed_reason)
                                <p class="mt-1 text-sm text-text-primary">{{ $document->failed_reason }}</p>
                                <p class="mt-2 text-xs text-text-secondary">Click "Reprocess OCR" to try again.</p>
                            @else
                                <p class="mt-1 text-sm text-text-primary">OCR gagal diproses. Klik "Reprocess OCR" untuk mencoba lagi.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- OCR Pending / Not started --}}
            @if($document->status === 'pending')
                <div class="rounded-xl border border-border bg-surface p-6 shadow-sm text-center">
                    <svg class="mx-auto h-10 w-10 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-3 text-sm font-medium text-text-primary">Menunggu OCR</p>
                    <p class="mt-1 text-xs text-text-secondary">Dokumen sudah siap. Klik "Lakukan OCR" untuk memproses.</p>
                    <form action="{{ route('documents.ocr', $document) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Lakukan OCR
                        </button>
                    </form>
                </div>
            @endif

            {{-- OCR Processing (shown when status=processing, waiting for result) --}}
            <template x-if="status === 'processing' && !timedOut">
                <div class="rounded-xl border border-border bg-surface p-6 shadow-sm text-center">
                    <svg class="mx-auto h-10 w-10 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-text-primary">OCR Sedang Diproses</p>
                    <p class="mt-1 text-xs text-text-secondary">Memeriksa status setiap 5 detik...</p>
                </div>
            </template>

            {{-- OCR Timeout --}}
            <template x-if="timedOut">
                <div class="rounded-xl border border-danger bg-danger-light bg-opacity-50 p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-6 w-6 shrink-0 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-danger">OCR Timeout</p>
                            <p class="mt-1 text-sm text-text-primary">OCR memakan waktu terlalu lama. Pastikan queue worker berjalan: <code class="rounded bg-gray-100 px-1 py-0.5 text-xs font-mono">php artisan queue:work</code></p>
                            <a href="{{ request()->url() }}" class="mt-3 inline-flex items-center gap-1 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                                Refresh Halaman
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            {{-- OCR Result --}}
            @if($document->ocrResult)
                <x-ocr-result-card :result="$document->ocrResult" />
            @endif

            {{-- Extract Academic Data --}}
            @if($document->status === 'ready' && $document->ocrResult)
                @if($document->academicRecord)
                    <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-text-secondary">Extracted Academic Data</h2>
                            <form action="{{ route('documents.re-extract-academic', $document) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Re-Extract Data
                                </button>
                            </form>
                        </div>

                        @php $ar = $document->academicRecord; @endphp
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                            <div>
                                <p class="text-xs text-text-secondary">Nama Mahasiswa</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->student_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">NIM</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->student_number ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">NIK</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->nik ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Universitas</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->university ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Fakultas</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->faculty ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Program Studi</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->study_program ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Gelar</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->gelar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Tanggal Lulus</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->tanggal_lulus ? $ar->tanggal_lulus->format('d M Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">IPK</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->gpa ? number_format($ar->gpa, 2) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Total SKS</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->total_sks ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Predikat Kelulusan</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->predikat_kelulusan ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Nomor Transkrip</p>
                                <p class="text-sm font-medium text-text-primary">{{ $ar->nomor_transkrip ?? '-' }}</p>
                            </div>
                        </div>

                        @if($ar->academicCourses->count() > 0)
                            <div class="mt-6">
                                <h3 class="mb-3 text-sm font-semibold text-text-secondary">Daftar Mata Kuliah</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-text-secondary">
                                                <th class="pb-2 pr-4 font-medium">No</th>
                                                <th class="pb-2 pr-4 font-medium">Mata Kuliah</th>
                                                <th class="pb-2 pr-4 font-medium">SKS</th>
                                                <th class="pb-2 font-medium">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            @foreach($ar->academicCourses as $index => $course)
                                                <tr class="text-text-primary">
                                                    <td class="py-2 pr-4 text-text-secondary">{{ $index + 1 }}</td>
                                                    <td class="py-2 pr-4 font-medium">{{ $course->nama_mata_kuliah }}</td>
                                                    <td class="py-2 pr-4">{{ $course->sks ?? '-' }}</td>
                                                    <td class="py-2">{{ $course->nilai ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">Extracted Academic Data</h2>
                        <p class="text-sm text-text-secondary mb-4">Ekstrak data akademik dari hasil OCR untuk mengisi informasi mahasiswa secara otomatis.</p>
                        <form action="{{ route('documents.extract-academic', $document) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Extract Academic Data
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">Verification</h2>

                @if($document->status === 'verified' || $document->status === 'rejected')
                    @php $lastVerification = $document->verifications->last(); @endphp
                    @if($lastVerification)
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$lastVerification->status" />
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Verified by</p>
                                <p class="text-sm font-medium text-text-primary">{{ $lastVerification->verifier?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary">Verified at</p>
                                <p class="text-sm font-medium text-text-primary">{{ $lastVerification->verified_at?->format('d M Y, H:i') ?? '-' }}</p>
                            </div>
                            @if($lastVerification->notes)
                                <div>
                                    <p class="text-xs text-text-secondary">Notes</p>
                                    <p class="text-sm text-text-primary">{{ $lastVerification->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @elseif($document->status === 'ready')
                    <form action="{{ route('documents.verify', $document) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">Decision</label>
                            <div class="flex gap-2">
                                <button type="submit" name="status" value="verified" class="flex-1 rounded-lg border border-success bg-success-light px-4 py-2 text-sm font-medium text-success transition-colors hover:bg-success hover:text-white">
                                    Verify
                                </button>
                                <button type="submit" name="status" value="rejected" class="flex-1 rounded-lg border border-danger bg-danger-light px-4 py-2 text-sm font-medium text-danger transition-colors hover:bg-danger hover:text-white">
                                    Reject
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary mb-1">Notes (optional)</label>
                            <textarea name="notes" rows="3" class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Add verification notes..."></textarea>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-text-secondary text-center py-4">Complete OCR processing before verification.</p>
                @endif
            </div>

            @if($document->verifications->count() > 0)
                <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">History</h2>
                    <div class="space-y-3">
                        @foreach($document->verifications as $ver)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                <div>
                                    <p class="text-sm font-medium text-text-primary">{{ $ver->verifier?->name ?? '-' }}</p>
                                    <p class="text-xs text-text-secondary">{{ $ver->verified_at?->diffForHumans() ?? '-' }}</p>
                                </div>
                                <x-status-badge :status="$ver->status" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection