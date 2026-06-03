<header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-border bg-surface px-6">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-text-secondary transition-colors hover:bg-gray-100 hover:text-text-primary">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search documents..." class="w-64 rounded-lg border border-border bg-bg py-2 pl-10 pr-4 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
            {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
        </div>
    </div>
</header>
