<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']) }}
      @switch($status)
          @case('verified')
              class="bg-success-light text-success"
              @break
          @case('rejected')
              class="bg-danger-light text-danger"
              @break
          @case('processing')
              class="bg-warning-light text-warning"
              @break
          @default
              class="bg-warning-light text-warning"
      @endswitch
>
    {{ $status === 'processing' ? 'Processing' : $status }}
</span>