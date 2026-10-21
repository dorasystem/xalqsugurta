@extends('layouts.app')
@section('title', __t('messages.claims.title'))

@section('content')
<x-insurence.flow icon="bi-life-preserver" :title="__t('messages.claims.title')">
    <div class="xf-panel">
        <div class="xf-panel__body">
            <x-insurence.found :title="__t('messages.claims.sent_title', ['number' => $number])" :text="__t('messages.claims.sent_text')" />
            <div>
                <a class="xf-btn xf-btn--primary" href="{{ route('claims.status', ['locale' => getCurrentLocale()]) }}">
                    <i class="bi bi-search"></i> {{ __t('messages.claims.check_status') }}
                </a>
            </div>
        </div>
    </div>
</x-insurence.flow>
@endsection
