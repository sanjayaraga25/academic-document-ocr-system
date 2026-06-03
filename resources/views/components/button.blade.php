<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed']) }}
        @switch($variant ?? 'primary')
            @case('secondary')
                class="border border-border bg-surface text-text-secondary hover:bg-gray-50 focus:ring-border"
                @break
            @case('danger')
                class="bg-danger text-white hover:bg-red-600 focus:ring-danger"
                @break
            @default
                class="bg-primary text-white hover:bg-primary-dark focus:ring-primary"
        @endswitch
>
    {{ $slot }}
</button>
