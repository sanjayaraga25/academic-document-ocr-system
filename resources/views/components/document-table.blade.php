<div class="overflow-hidden rounded-xl border border-border bg-surface" x-data="{ sortField: 'created_at', sortDir: 'desc' }">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-border bg-gray-50/50">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary cursor-pointer hover:text-text-primary"
                        @click="sortField = 'filename'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'">
                        Document
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary cursor-pointer hover:text-text-primary"
                        @click="sortField = 'created_at'; sortDir = sortDir === 'asc' ? 'desc' : 'asc'">
                        Uploaded
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-text-secondary">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($documents as $doc)
                    <tr class="transition-colors hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <svg class="h-8 w-8 shrink-0 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div>
                                    <p class="text-sm font-medium text-text-primary">{{ $doc->original_filename }}</p>
                                    <p class="text-xs text-text-secondary">#{{ $doc->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium uppercase text-text-secondary">{{ $doc->file_type }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$doc->status" />
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-text-secondary">{{ $doc->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('documents.show', $doc) }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-primary transition-colors hover:bg-primary-light">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-text-secondary">
                            No documents found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($documents, 'links'))
        <div class="border-t border-border px-4 py-3">
            {{ $documents->links() }}
        </div>
    @endif
</div>
