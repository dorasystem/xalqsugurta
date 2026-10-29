@extends('layouts.app')
@section('title', $vacancy->title($locale))

{{-- One vacancy, design "C": the list with this one marked, its text, the application form beside. $body = clean HTML --}}
@section('content')
<div class="in">
    <x-inner.head :title="$vacancy->title($locale)" :crumb="[__t('messages.career.title'), route('career.index', ['locale' => $locale])]"
        :lead="collect([
            $vacancy->location,
            $vacancy->employment ? __t('messages.career.employment_' . $vacancy->employment) : null,
            $vacancy->deadline_at ? __t('messages.career.deadline', ['date' => $vacancy->deadline_at->format('d.m.Y')]) : null,
        ])->filter()->join(' · ')" />

    <div class="in-wrap in-cols">
        <div class="in-main">
            @if ($body)
                <article class="in-panel in-prose" id="vacancy">{!! $body !!}</article>
            @endif
            @if ($vacancies->count() > 1)
                <h2 class="in-section-title">{{ __t('messages.career.open') }}</h2>
                @include('pages.career._list', ['active' => $vacancy])
            @endif
        </div>
        <aside class="in-side">
            @include('pages.career._form', ['vacancy' => $vacancy])
        </aside>
    </div>
</div>
@endsection
