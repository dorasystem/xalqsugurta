@extends('layouts.app')
@section('title', __('messages.welcome'))
@section('content')
    <div class="container">
        <h1>{{ __('messages.welcome') }} - Xalq Sugurta</h1>
        <p>{{ __('messages.insurance') }} {{ __('messages.services') }}</p>
        <div class="language-info">
            <p>{{ __('messages.language') }}: {{ getCurrentLocale() }}</p>
        </div>

        <a href="{{ route('osago', ['locale' => getCurrentLocale()]) }}">{{ __('messages.osago') }}</a>
    </div>
@endsection
