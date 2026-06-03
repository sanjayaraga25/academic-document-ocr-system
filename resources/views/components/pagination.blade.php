<div class="flex items-center justify-between">
    <p class="text-sm text-text-secondary">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </p>
    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg px-3 py-1.5 text-sm text-text-secondary opacity-50 cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-100 hover:text-text-primary">Previous</a>
        @endif
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $page === $paginator->currentPage() ? 'bg-primary text-white' : 'text-text-secondary hover:bg-gray-100 hover:text-text-primary' }}">{{ $page }}</a>
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-100 hover:text-text-primary">Next</a>
        @else
            <span class="rounded-lg px-3 py-1.5 text-sm text-text-secondary opacity-50 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
