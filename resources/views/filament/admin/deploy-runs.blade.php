{{-- Latest GitHub Actions deploy runs (App\Services\Admin\DeployTrigger::runs()); polls while one runs --}}
@php
    $label = fn (array $r) => match (true) {
        in_array($r['status'], ['queued', 'waiting', 'pending', 'requested'], true) => ['Navbatda', 'warning'],
        $r['status'] === 'in_progress' => ['Bajarilmoqda…', 'warning'],
        $r['conclusion'] === 'success' => ['Muvaffaqiyatli', 'success'],
        $r['conclusion'] === 'cancelled' => ['Bekor qilingan', 'gray'],
        default => ['Xato', 'danger'],
    };
    $events = ['push' => 'merge', 'workflow_dispatch' => 'admin paneldan'];
    $active = collect($runs)->contains(fn ($r) => $r['status'] !== 'completed');
@endphp
<div class="xs-runs">
    @if ($active)
        <div wire:poll.10s></div>
    @endif

    @if (!$ready)
        <p class="xs-runs__meta">Tugma ishlashi uchun serverdagi .env ga GITHUB_DEPLOY_TOKEN yozing va "Keshni yangilash"ni bosing.</p>
    @else
        @forelse ($runs as $run)
            @php [$text, $color] = $label($run); @endphp
            <div class="xs-runs__item xs-runs__row">
                <x-filament::badge :color="$color">{{ $text }}</x-filament::badge>
                <span class="xs-runs__meta">
                    {{ $run['created_at'] ? \Illuminate\Support\Carbon::parse($run['created_at'])->timezone(config('app.timezone'))->format('d.m.Y H:i') : '' }}
                    · {{ $events[$run['event']] ?? $run['event'] }} · {{ $run['sha'] }}
                </span>
                @if ($run['url'])
                    <x-filament::link :href="$run['url']" target="_blank" icon="heroicon-o-arrow-top-right-on-square" size="sm">GitHub</x-filament::link>
                @endif
            </div>
        @empty
            <p class="xs-runs__meta">GitHub'dan deploylar ro'yxati olinmadi.</p>
        @endforelse
    @endif
</div>
