@extends('layouts.app')
@section('title', __t('messages.callback.title'))

@php $locale = getCurrentLocale(); @endphp

@section('content')
<x-insurence.flow icon="bi-telephone-inbound" :title="__t('messages.callback.title')" :subtitle="__t('messages.callback.subtitle')">

    @if (session('status'))
        <x-insurence.found :title="session('status')" :text="__t('messages.callback.sent_text')" />
    @endif

    <form action="{{ route('callback.store', ['locale' => $locale]) }}" method="POST" class="xf-panel">
        @csrf
        <div class="visually-hidden" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>
        <div class="xf-panel__body">
            <div class="xf-row">
                <x-insurence.field name="name" :label="__t('messages.callback.name')" maxlength="100" autocomplete="name" />
                <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
            </div>
            <div class="xf-field">
                <span class="xf-field__label" id="topic_label">{{ __t('messages.callback.topic') }}</span>
                <div class="xf-chips" role="radiogroup" aria-labelledby="topic_label">
                    @foreach ($topics as $topic)
                        <label class="xf-chip">
                            <input type="radio" name="topic" value="{{ $topic }}" class="visually-hidden" @checked(old('topic', 'buy') === $topic)>
                            {{ __t('messages.callback.topic_' . $topic) }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="xf-field">
                <label for="f_message" class="xf-field__label">{{ __t('messages.callback.message') }}</label>
                <textarea name="message" id="f_message" class="xf-input" rows="3" maxlength="1000">{{ old('message') }}</textarea>
            </div>
        </div>
        <x-insurence.actions :payment="false" :submit="__t('messages.callback.send')" />
    </form>

</x-insurence.flow>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="topic"]').forEach(function (r) {
    function sync() {
        document.querySelectorAll('input[name="topic"]').forEach(function (x) {
            x.closest('.xf-chip').setAttribute('aria-pressed', x.checked ? 'true' : 'false');
        });
    }
    r.addEventListener('change', sync);
    sync();
});
</script>
@endpush
