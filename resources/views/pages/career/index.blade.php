@extends('layouts.app')
@section('title', __t('messages.career.title'))

{{-- Career, design "C": open vacancies, the CV form always beside them. $vacancies = open Vacancy rows --}}
@section('content')
<div class="in">
    <x-inner.head :title="__t('messages.career.title')" :lead="__t('messages.career.subtitle')" />

    <div class="in-wrap in-cols">
        <div class="in-main">
            @include('pages.career._list', ['active' => null])
        </div>
        <aside class="in-side">
            @include('pages.career._form', ['vacancy' => null])
        </aside>
    </div>
</div>
@endsection
