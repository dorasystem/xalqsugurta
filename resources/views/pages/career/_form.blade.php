{{-- CV form in the side column; $vacancy = the vacancy applied for, or null for a general CV --}}
<form action="{{ route('career.apply', ['locale' => $locale]) }}" method="POST" enctype="multipart/form-data" class="in-panel in-form in-form-card" id="apply">
    @csrf
    <div class="in-sr" aria-hidden="true">
        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    @if ($vacancy)
        <input type="hidden" name="vacancy_id" value="{{ $vacancy->id }}">
    @endif

    <div>
        <h2 class="in-panel__title">{{ $vacancy ? __t('messages.career.apply_title') : __t('messages.career.cv_title') }}</h2>
        <p class="in-meta">{{ $vacancy ? $vacancy->title($locale) : __t('messages.career.cv_text') }}</p>
    </div>

    @if (session('status'))
        <div class="in-ok" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <div><b>{{ session('status') }}</b><span>{{ __t('messages.career.sent_text') }}</span></div>
        </div>
    @endif

    @foreach ([
        ['name', 'text', __t('messages.career.name'), ['maxlength' => 100, 'autocomplete' => 'name']],
        ['phone', 'tel', __('messages.phone_number'), ['inputmode' => 'tel', 'placeholder' => '+998 90 123 45 67', 'autocomplete' => 'tel']],
        ['email', 'email', __t('messages.career.email'), ['maxlength' => 150, 'autocomplete' => 'email']],
    ] as [$field, $type, $label, $attrs])
        <label class="in-field" for="f_{{ $field }}">
            {{ $label }}
            <input type="{{ $type }}" name="{{ $field }}" id="f_{{ $field }}" value="{{ old($field) }}" @class(['in-input', 'is-invalid' => $errors->has($field)])
                @foreach ($attrs as $k => $v) {{ $k }}="{{ $v }}" @endforeach>
            @error($field)<p class="in-error">{{ $message }}</p>@enderror
        </label>
    @endforeach

    <label class="in-field" for="f_resume">
        {{ __t('messages.career.resume') }}
        <input type="file" name="resume" id="f_resume" accept=".pdf,.doc,.docx" @class(['in-input', 'is-invalid' => $errors->has('resume')])>
        @error('resume')<p class="in-error">{{ $message }}</p>@else<span class="in-help">{{ __t('messages.career.resume_help') }}</span>@enderror
    </label>

    <label class="in-field" for="f_message">
        {{ __t('messages.career.message') }}
        <textarea name="message" id="f_message" class="in-input" rows="3" maxlength="2000">{{ old('message') }}</textarea>
    </label>

    <button type="submit" class="in-submit">{{ __t('messages.career.send') }}</button>
</form>
