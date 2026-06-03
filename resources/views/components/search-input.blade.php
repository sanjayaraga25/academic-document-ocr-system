<div class="relative">
    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" placeholder="{{ $placeholder ?? 'Search...' }}"
           {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-bg py-2 pl-10 pr-4 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary']) }}>
</div>
