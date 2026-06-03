<div x-data="{ show: false }"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     x-transition:enter="transition duration-200 ease-out"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition duration-150 ease-in"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">
    <div class="fixed inset-0 bg-black/50" @click="show = false"></div>
    <div class="relative z-10 w-full max-w-lg rounded-xl bg-surface p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-text-primary">{{ $title ?? 'Modal' }}</h3>
            <button @click="show = false" class="rounded-lg p-1 text-text-secondary hover:bg-gray-100 hover:text-text-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
