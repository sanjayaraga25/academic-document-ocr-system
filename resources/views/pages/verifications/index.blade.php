@extends('layouts.app')

@section('title', 'Verifications')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Verifications</h1>
        <p class="mt-1 text-sm text-text-secondary">Review and verify OCR-processed documents</p>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <select name="status" class="rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="ready" @selected(request('status') === 'ready')>Ready</option>
        </select>
    </div>

    <div class="space-y-4">
        @forelse($documents as $doc)
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <svg class="h-10 w-10 shrink-0 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-text-primary">{{ $doc->original_filename }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <x-status-badge :status="$doc->status" />
                                <span class="text-xs text-text-secondary">by {{ $doc->uploader?->name ?? '-' }}</span>
                                <span class="text-xs text-text-secondary">•</span>
                                <span class="text-xs text-text-secondary">{{ $doc->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($doc->ocrResult)
                            <x-confidence-badge :score="$doc->ocrResult->confidence_score" />
                        @endif
                        <a href="{{ route('documents.show', $doc) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                            Review
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-border bg-surface p-12 text-center shadow-sm">
                <svg class="mx-auto h-10 w-10 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="mt-3 text-sm font-medium text-text-primary">No documents to verify</p>
                <p class="mt-1 text-xs text-text-secondary">Upload documents first to start verification.</p>
            </div>
        @endforelse
    </div>

    @if(method_exists($documents, 'links'))
        <div class="mt-6">
            {{ $documents->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.querySelector('[name="status"]')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('status', this.value);
        window.location.href = url.toString();
    });
</script>
@endpush
