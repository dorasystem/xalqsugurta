@extends('layouts.app')
@section('title', $vacancy->title($locale))

{{-- One vacancy and its application form. $body = cleaned HTML --}}
@section('content')
<x-insurence.flow icon="bi-briefcase" :title="$vacancy->title($locale)" :subtitle="collect([
        $vacancy->location,
        $vacancy->employment ? __t('messages.career.employment_' . $vacancy->employment) : null,
        $vacancy->deadline_at ? __t('messages.career.deadline', ['date' => $vacancy->deadline_at->format('d.m.Y')]) : null,
    ])->filter()->join(' · ')">

    @if ($body)
        <section class="xf-panel">
            <div class="xf-panel__body xf-prose">{!! $body !!}</div>
        </section>
    @endif

    @include('pages.career._form', ['vacancy' => $vacancy])

    <p><a href="{{ route('career.index', ['locale' => $locale]) }}" class="xf-btn xf-btn--ghost"><i class="bi bi-arrow-left"></i> {{ __t('messages.career.all') }}</a></p>

</x-insurence.flow>
@endsection
