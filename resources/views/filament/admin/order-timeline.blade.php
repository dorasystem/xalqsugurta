{{-- Order card "Jarayon": application → contract → payment → policy (OrderResource::timeline) --}}
@php
    $icons = ['ok' => '✓', 'fail' => '!', 'wait' => '…', 'todo' => ''];
@endphp
<ol class="xs-timeline">
    @foreach (\App\Filament\Admin\Resources\OrderResource::timeline($getRecord()) as [$state, $title, $detail, $time])
        <li class="xs-timeline__step is-{{ $state }}">
            <span class="xs-timeline__dot" aria-hidden="true">{{ $icons[$state] }}</span>
            <span class="xs-timeline__body">
                <b>{{ $title }}</b>
                @if ($detail)
                    <span>{{ $detail }}</span>
                @endif
            </span>
            @if ($time)
                <time datetime="{{ $time->toIso8601String() }}">{{ $time->format('d.m H:i') }}</time>
            @endif
        </li>
    @endforeach
</ol>
