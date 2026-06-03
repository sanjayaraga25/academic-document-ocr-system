@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-text-primary">Dashboard</h1>
        <p class="mt-1 text-sm text-text-secondary">Overview of your document verification system</p>
    </div>

    {{-- Queue Health Warning --}}
    @if($stuckDocuments > 0)
        <div class="mb-6 rounded-xl border border-danger bg-danger-light bg-opacity-50 p-4 shadow-sm flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-danger">Queue Worker Tidak Aktif</p>
                <p class="text-xs text-text-secondary mt-1">
                    {{ $stuckDocuments }} dokumen masih dalam status "processing" lebih dari 2 menit.
                    Jalankan <code class="rounded bg-danger-light bg-opacity-50 px-1 py-0.5 text-xs font-mono">php artisan queue:work</code>
                    untuk memproses antrian OCR.
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard-card label="Total Documents" :value="$stats['total']" iconBg="bg-primary-light">
            <x-slot:icon>
                <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-dashboard-card>

        <x-dashboard-card label="Verified" :value="$stats['verified']" iconBg="bg-success-light">
            <x-slot:icon>
                <svg class="h-6 w-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-dashboard-card>

        <x-dashboard-card label="Rejected" :value="$stats['rejected']" iconBg="bg-danger-light">
            <x-slot:icon>
                <svg class="h-6 w-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-dashboard-card>

        <x-dashboard-card label="Processing" :value="$stats['processing']" iconBg="bg-warning-light">
            <x-slot:icon>
                <svg class="h-6 w-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-dashboard-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">Recent Documents</h2>
            <div class="space-y-3">
                @forelse($recentDocuments as $doc)
                    <a href="{{ route('documents.show', $doc) }}" class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <svg class="h-8 w-8 shrink-0 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-text-primary">{{ $doc->original_filename }}</p>
                                <p class="text-xs text-text-secondary">{{ $doc->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <x-status-badge :status="$doc->status" />
                    </a>
                @empty
                    <p class="text-sm text-text-secondary text-center py-8">No documents yet</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-text-secondary">7-Day Trend</h2>
            <div class="flex items-end gap-2 h-40">
                @forelse($verificationTrend as $day)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="w-full rounded-md bg-primary-light transition-all duration-300 hover:bg-primary" style="height: {{ max($day->total * 20, 4) }}px;"></div>
                        <span class="text-xs text-text-secondary">{{ \Carbon\Carbon::parse($day->date)->format('d') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-text-secondary text-center w-full">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection