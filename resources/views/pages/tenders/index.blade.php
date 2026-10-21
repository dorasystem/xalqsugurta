@extends('layouts.app')
@section('title', __t('messages.tenders.title'))

{{-- Tenders. $open / $closed = Tender collections from TenderController --}}
@section('content')
<x-insurence.flow icon="bi-megaphone" :title="__t('messages.tenders.title')" :subtitle="__t('messages.tenders.subtitle')">

    @foreach (['open' => $open, 'closed' => $closed] as $state => $list)
        @continue($state === 'closed' && $list->isEmpty())
        <section class="xf-panel">
            <div class="xf-panel__head">
                <h2 class="xf-panel__title">{{ __t('messages.tenders.' . $state) }}</h2>
            </div>
            <div class="xf-panel__body">
                @forelse ($list as $tender)
                    <a class="xf-search-hit" href="{{ route('tenders.show', ['locale' => $locale, 'slug' => $tender->slug]) }}">
                        <span class="xf-search-hit__icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span>
                            <strong>{{ $tender->title($locale) }}</strong>
                            <small>
                                {{ __t('messages.tenders.published', ['date' => $tender->published_at->format('d.m.Y')]) }}
                                @if ($tender->deadline_at)
                                    · {{ __t('messages.tenders.deadline', ['date' => $tender->deadline_at->format('d.m.Y')]) }}
                                @endif
                            </small>
                        </span>
                        <span class="xf-tender-status xf-tender-status--{{ $state }}">{{ __t('messages.tenders.status_' . $state) }}</span>
                    </a>
                @empty
                    <p class="xf-note">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ __t('messages.tenders.none') }}</span>
                    </p>
                @endforelse
            </div>
        </section>
    @endforeach

</x-insurence.flow>
@endsection
