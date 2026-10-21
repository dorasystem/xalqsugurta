{{-- Dashboard "Diqqat talab qiladi" (App\Filament\Admin\Widgets\AttentionWidget) --}}
@php
    $paths = [
        'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6z"/><path d="M12 9v4M12 16v.5"/>',
        'alert'  => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/>',
        'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check'  => '<path d="M5 12l5 5 9-10"/>',
    ];
@endphp
<x-filament-widgets::widget>
    <x-filament::section heading="Diqqat talab qiladi" icon="heroicon-o-bell-alert">
        <div class="xs-attention">
            @forelse ($items as $item)
                <a href="{{ $item['url'] }}" class="xs-attention__item">
                    <span class="xs-attention__icon is-{{ $item['tone'] }}" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $paths[$item['icon']] !!}</svg>
                    </span>
                    <span class="xs-attention__text">
                        <b>{{ $item['title'] }}</b>
                        <span>{{ $item['text'] }}</span>
                    </span>
                    <span class="xs-attention__count">{{ $item['count'] }}</span>
                </a>
            @empty
                <div class="xs-attention__item">
                    <span class="xs-attention__icon is-ok" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $paths['check'] !!}</svg>
                    </span>
                    <span class="xs-attention__text">
                        <b>Hammasi joyida</b>
                        <span>Polissiz to'lovlar, API xatolari va uzoq kutilayotgan to'lovlar yo'q</span>
                    </span>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
