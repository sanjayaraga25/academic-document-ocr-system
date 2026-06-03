<div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-text-secondary">OCR Result</h3>
        <x-confidence-badge :score="$result->confidence_score" />
    </div>



    <details class="mt-4">
        <summary class="cursor-pointer text-sm font-medium text-primary hover:text-primary-dark">View Raw Text</summary>
        <pre class="mt-2 max-h-48 overflow-y-auto rounded-lg bg-gray-50 p-3 text-xs text-text-secondary">{{ $result->raw_text }}</pre>
    </details>

    <p class="mt-3 text-xs text-text-secondary">Processing time: {{ number_format($result->processing_time, 2) }}s</p>
</div>
