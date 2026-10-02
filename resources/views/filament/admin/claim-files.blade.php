{{-- Files attached to a claim: links are signed for 30 minutes and need an admin session --}}
@php
    $claim = $getRecord();
    $files = $claim?->files ?? [];
@endphp

<div>
    <p class="fi-sc-text" style="font-weight: 600; margin-bottom: 6px">Fayllar</p>
    @forelse ($files as $i => $file)
        <div style="display: flex; gap: 10px; align-items: center; padding: 4px 0">
            <x-filament::link
                :href="URL::temporarySignedRoute('claims.file', now()->addMinutes(30), ['claim' => $claim->id, 'index' => $i])"
                target="_blank"
                icon="heroicon-o-arrow-down-tray"
            >
                {{ $file['name'] ?? ('fayl-' . ($i + 1)) }}
            </x-filament::link>
            <span style="color: #7b7896; font-size: 12px">{{ number_format(($file['size'] ?? 0) / 1024, 0, '.', ' ') }} KB</span>
        </div>
    @empty
        <p style="color: #7b7896">Fayl biriktirilmagan.</p>
    @endforelse
</div>
