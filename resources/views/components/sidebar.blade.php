<aside class="fixed left-0 top-0 z-40 flex h-screen flex-col border-r border-border bg-surface transition-all duration-300"
       :class="sidebarOpen ? 'w-[280px]' : 'w-0 overflow-hidden'">
    <div class="flex h-[72px] items-center gap-3 border-b border-border px-6">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white font-bold text-lg">
            O
        </div>
        <div class="flex flex-col">
            <span class="text-sm font-semibold text-text-primary">Academic OCR</span>
            <span class="text-xs text-text-secondary">Verification System</span>
        </div>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto p-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(request()->routeIs('dashboard')) bg-primary-light text-primary @else text-text-secondary hover:bg-gray-100 hover:text-text-primary @endif">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('documents.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(request()->routeIs('documents.*')) bg-primary-light text-primary @else text-text-secondary hover:bg-gray-100 hover:text-text-primary @endif">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Documents
        </a>
        <a href="{{ route('documents.upload') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(request()->routeIs('documents.upload')) bg-primary-light text-primary @else text-text-secondary hover:bg-gray-100 hover:text-text-primary @endif">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload
        </a>
        <a href="{{ route('verifications.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(request()->routeIs('verifications.*')) bg-primary-light text-primary @else text-text-secondary hover:bg-gray-100 hover:text-text-primary @endif">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifications
        </a>
        <a href="{{ route('qualification.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors @if(request()->routeIs('qualification.*')) bg-primary-light text-primary @else text-text-secondary hover:bg-gray-100 hover:text-text-primary @endif">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Qualification
        </a>
    </nav>
    <div class="border-t border-border p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-red-50 hover:text-danger">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>
</aside>
