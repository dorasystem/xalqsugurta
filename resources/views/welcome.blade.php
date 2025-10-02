@extends('layouts.app')
@section('title', __('messages.welcome'))
@section('content')
    <section class="section-header my-4">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <div class="card card-body p-3">
                        <h1>{{ __('messages.welcome') }} - Xalq Sugurta</h1>
                        <p>{{ __('messages.insurance') }} {{ __('messages.services') }}</p>
                        <a class="border px-3 py-2 rounded-md text-center" href="{{ route('osago.main', ['locale' => getCurrentLocale()]) }}">{{ __('messages.osago') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
