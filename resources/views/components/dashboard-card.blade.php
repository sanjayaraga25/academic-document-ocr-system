<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-surface p-6 shadow-sm transition-all duration-200 hover:shadow-md']) }}>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-text-secondary">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-text-primary">{{ $value }}</p>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $iconBg ?? 'bg-primary-light' }}">
            {{ $icon ?? '' }}
        </div>
    </div>
    @isset($change)
        <div class="mt-4 flex items-center gap-1 text-sm">
            {{ $change }}
        </div>
    @endisset
</div>
