@extends('layouts.app')

@section('title', 'Documents')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Documents</h1>
            <p class="mt-1 text-sm text-text-secondary">Manage your uploaded documents</p>
        </div>
        <a href="{{ route('documents.upload') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Upload Document
        </a>
    </div>

    <div class="mb-4 flex items-center gap-3">
        <x-search-input name="search" placeholder="Search by filename or student name..." :value="request('search')" />
        <select name="status" class="rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="ready" @selected(request('status') === 'ready')>Ready</option>
            <option value="verified" @selected(request('status') === 'verified')>Verified</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
    </div>

    <x-document-table :documents="$documents" />
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[name="search"], [name="status"]').forEach(el => {
        el.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('search', document.querySelector('[name="search"]').value);
            url.searchParams.set('status', document.querySelector('[name="status"]').value);
            window.location.href = url.toString();
        });
    });
</script>
@endpush
