{{-- History of server commands (App\Filament\Admin\Pages\ServerCommandsPage); polls while one runs --}}
@php
    $badge = [
        \App\Models\CommandRun::STATUS_RUNNING => ['Bajarilmoqda…', 'warning'],
        \App\Models\CommandRun::STATUS_DONE    => ['Bajarildi', 'success'],
        \App\Models\CommandRun::STATUS_FAILED  => ['Xato', 'danger'],
    ];
@endphp
<x-filament::section heading="Oxirgi buyruqlar" icon="heroicon-o-clock">
    @if ($running)
        <div wire:poll.3s></div>
    @endif

    <div class="xs-runs">
        @forelse ($runs as $run)
            @php
                [$label, $color] = $badge[$run->status] ?? [$run->status, 'gray'];
                $stale = $run->status === \App\Models\CommandRun::STATUS_RUNNING && $run->started_at?->lt(now()->subMinutes(\App\Services\Admin\ServerCommands::STALE_MINUTES));
            @endphp
            <details class="xs-runs__item" @if ($loop->first) open @endif>
                <summary>
                    <b>{{ \App\Services\Admin\ServerCommands::label($run->command) }}</b>
                    <x-filament::badge :color="$stale ? 'gray' : $color">{{ $stale ? 'To\'xtab qolgan' : $label }}</x-filament::badge>
                    <span class="xs-runs__meta">
                        {{ $run->started_at?->format('d.m.Y H:i:s') }}
                        @if ($run->finished_at) · {{ $run->started_at->diffInSeconds($run->finished_at) }} s @endif
                        · {{ $run->user?->name ?? '—' }}
                    </span>
                </summary>
                <pre class="xs-runs__output">{{ $run->output ?: '…' }}</pre>
            </details>
        @empty
            <p class="xs-runs__meta">Hali buyruq bajarilmagan.</p>
        @endforelse
    </div>
</x-filament::section>
