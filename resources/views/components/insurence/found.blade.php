@props([
    'title' => '',
    'text'  => '',
])

<div {{ $attributes->class('xf-found') }}>
    <span class="xf-found__check"><i class="bi bi-check-lg"></i></span>
    <div>
        <b class="xf-found__title" data-found="title">{{ $title }}</b>
        <span class="xf-found__text" data-found="text">{{ $text }}</span>
        {{ $slot }}
    </div>
</div>
