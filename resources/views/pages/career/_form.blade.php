{{-- CV form; $vacancy = the vacancy applied for, or null for a general CV --}}
<form action="{{ route('career.apply', ['locale' => $locale]) }}" method="POST" enctype="multipart/form-data" class="xf-panel" id="apply">
    @csrf
    <div class="visually-hidden" aria-hidden="true">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    @if ($vacancy)
        <input type="hidden" name="vacancy_id" value="{{ $vacancy->id }}">
    @endif
    <div class="xf-panel__head">
        <h2 class="xf-panel__title">{{ $vacancy ? __t('messages.career.apply_title') : __t('messages.career.cv_title') }}</h2>
        <p class="xf-panel__subtitle">{{ $vacancy ? $vacancy->title($locale) : __t('messages.career.cv_text') }}</p>
    </div>
    <div class="xf-panel__body">
        @if (session('status'))
            <x-insurence.found :title="session('status')" :text="__t('messages.career.sent_text')" />
        @endif
        <div class="xf-row">
            <x-insurence.field name="name" :label="__t('messages.career.name')" maxlength="100" autocomplete="name" />
            <x-insurence.field name="phone" type="tel" :label="__('messages.phone_number')" inputmode="tel" placeholder="+998 90 123 45 67" autocomplete="tel" />
        </div>
        <x-insurence.field name="email" type="email" :label="__t('messages.career.email')" maxlength="150" autocomplete="email" />
        <x-insurence.field name="resume" type="file" :label="__t('messages.career.resume')" :help="__t('messages.career.resume_help')" accept=".pdf,.doc,.docx" />
        <div class="xf-field">
            <label for="f_message" class="xf-field__label">{{ __t('messages.career.message') }}</label>
            <textarea name="message" id="f_message" class="xf-input" rows="3" maxlength="2000">{{ old('message') }}</textarea>
        </div>
    </div>
    <x-insurence.actions :payment="false" :submit="__t('messages.career.send')" />
</form>
