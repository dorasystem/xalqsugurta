@extends('layouts.app')
@section('title', __t('messages.career.title'))

{{-- Career section. $vacancies = open Vacancy rows --}}
@section('content')
<x-insurence.flow icon="bi-briefcase" :title="__t('messages.career.title')" :subtitle="__t('messages.career.subtitle')">

    <section class="xf-panel">
        <div class="xf-panel__head">
            <h2 class="xf-panel__title">{{ __t('messages.career.open') }}</h2>
        </div>
        <div class="xf-panel__body">
            @forelse ($vacancies as $vacancy)
                <a class="xf-search-hit" href="{{ route('career.show', ['locale' => $locale, 'slug' => $vacancy->slug]) }}">
                    <span class="xf-search-hit__icon"><i class="bi bi-person-workspace"></i></span>
                    <span>
                        <strong>{{ $vacancy->title($locale) }}</strong>
                        <small>
                            {{ collect([
                                $vacancy->location,
                                $vacancy->employment ? __t('messages.career.employment_' . $vacancy->employment) : null,
                                $vacancy->deadline_at ? __t('messages.career.deadline', ['date' => $vacancy->deadline_at->format('d.m.Y')]) : null,
                            ])->filter()->join(' · ') }}
                        </small>
                    </span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            @empty
                <p class="xf-note">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __t('messages.career.none') }}</span>
                </p>
            @endforelse
        </div>
    </section>

    @include('pages.career._form', ['vacancy' => null])

</x-insurence.flow>
@endsection
