@extends('layouts.app')
@section('title', $tender->title($locale))

{{-- One tender. $body = Tender::body() (clean HTML) --}}
@section('content')
<x-insurence.flow icon="bi-megaphone" :title="$tender->title($locale)" :subtitle="__t('messages.tenders.title')">

    <section class="xf-panel">
        <div class="xf-panel__body xf-prose">{!! $body !!}</div>
    </section>

    <x-slot:summary>
        <aside class="xf-panel xf-summary">
            <div class="xf-summary__top">
                <small>{{ __t('messages.tenders.status') }}</small>
                <span class="xf-tender-status xf-tender-status--{{ $tender->isOpen() ? 'open' : 'closed' }}">
                    {{ __t('messages.tenders.status_' . ($tender->isOpen() ? 'open' : 'closed')) }}
                </span>
            </div>
            <ul class="xf-summary__list">
                <li><span>{{ __t('messages.tenders.published_label') }}</span><b>{{ $tender->published_at->format('d.m.Y') }}</b></li>
                @if ($tender->deadline_at)
                    <li><span>{{ __t('messages.tenders.deadline_label') }}</span><b>{{ $tender->deadline_at->format('d.m.Y') }}</b></li>
                @endif
                @if ($tender->email)
                    <li class="xf-tender-email"><span>{{ __t('messages.tenders.email_label') }}</span><b><a href="mailto:{{ $tender->email }}">{{ $tender->email }}</a></b></li>
                @endif
            </ul>

            <div class="xf-summary__help">
                <i class="bi bi-arrow-left"></i>
                <a href="{{ route('tenders.index', ['locale' => $locale]) }}">{{ __t('messages.tenders.all') }}</a>
            </div>
        </aside>
    </x-slot:summary>

</x-insurence.flow>
@endsection
