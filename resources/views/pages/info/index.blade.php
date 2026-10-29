@extends('layouts.app')
@section('title', __t('messages.info_page.hub_title'))

{{-- Information centre: every published company page, by section. $sections = section => pages --}}
@section('content')
<div class="in">
    <x-inner.head :title="__t('messages.info_page.hub_title')" :lead="__t('messages.info_page.hub_lead')" />

    <div class="in-wrap">
        @forelse ($sections as $section => $pages)
            <h2 class="in-section-title" id="{{ $section }}">{{ __t('messages.info_page.sections.' . $section) }}</h2>
            @include('pages.info._tiles', ['pages' => $pages, 'active' => null])
        @empty
            <p class="in-empty">{{ __t('messages.info_page.none') }}</p>
        @endforelse
    </div>
</div>
@endsection
