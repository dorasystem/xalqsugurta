{{-- "How to send a proposal" panel; $email = address for proposals --}}
<div class="in-dark">
    <h2 class="in-dark__title">{{ __t('messages.tenders.how_title') }}</h2>
    <ol class="in-steps">
        @foreach (['how_1', 'how_2', 'how_3'] as $i => $step)
            <li><b>{{ $i + 1 }}</b><span>{{ __t('messages.tenders.' . $step) }}</span></li>
        @endforeach
    </ol>
    <a class="in-mail" href="mailto:{{ $email }}"><i class="bi bi-envelope" aria-hidden="true"></i> {{ $email }}</a>
</div>
