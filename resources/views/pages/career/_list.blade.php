{{-- Open vacancies as rows; $vacancies, $active = the vacancy shown (or null) --}}
@forelse ($vacancies as $item)
    <a @class(['in-vrow', 'is-active' => $active?->is($item)]) href="{{ route('career.show', ['locale' => $locale, 'slug' => $item->slug]) }}#vacancy"
        @if ($active?->is($item)) aria-current="page" @endif>
        <span>
            <b>{{ $item->title($locale) }}</b>
            <span class="in-meta">
                {{ collect([
                    $item->location,
                    $item->employment ? __t('messages.career.employment_' . $item->employment) : null,
                    $item->deadline_at ? __t('messages.career.deadline', ['date' => $item->deadline_at->format('d.m.Y')]) : null,
                ])->filter()->join(' · ') }}
            </span>
        </span>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
    </a>
@empty
    <p class="in-empty">{{ __t('messages.career.none') }}</p>
@endforelse
