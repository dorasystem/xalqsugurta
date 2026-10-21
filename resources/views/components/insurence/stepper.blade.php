@props([
    'steps'   => [],
    'current' => 1,
    'urls'    => [],
])

<ol class="xf-stepper">
    @foreach ($steps as $i => $label)
        @php
            $n     = $i + 1;
            $state = $n < $current ? 'is-done' : ($n === $current ? 'is-current' : '');
            $url   = $n < $current ? ($urls[$i] ?? null) : null;
        @endphp
        <li class="xf-stepper__item {{ $state }}" @if ($n === $current) aria-current="step" @endif>
            @if ($url)<a href="{{ $url }}" class="xf-stepper__link">@else<span class="xf-stepper__link">@endif
                <span class="xf-stepper__dot">
                    @if ($n < $current)
                        <i class="bi bi-check-lg"></i>
                    @else
                        {{ $n }}
                    @endif
                </span>
                <span class="xf-stepper__label">{{ $label }}</span>
            @if ($url)</a>@else</span>@endif
        </li>
    @endforeach
</ol>
<p class="xf-stepper__count">{{ __t('messages.flow.step_of', ['current' => $current, 'total' => count($steps)]) }}</p>
