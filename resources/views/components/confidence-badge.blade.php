<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']) }}
      @if($score >= 80)
          class="bg-success-light text-success"
      @elseif($score >= 60)
          class="bg-warning-light text-warning"
      @else
          class="bg-danger-light text-danger"
      @endif
>
    {{ $score }}%
</span>
