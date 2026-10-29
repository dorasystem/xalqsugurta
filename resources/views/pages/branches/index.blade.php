@extends('layouts.app')
@section('title', __t('messages.branches.title'))

{{-- Branches. $branches = active Branch rows --}}
@section('content')
<x-insurence.flow icon="bi-geo-alt" :title="__t('messages.branches.title')" :subtitle="__t('messages.branches.subtitle', ['count' => $branches->count()])">

    @if ($branches->isEmpty())
        <section class="xf-panel">
            <div class="xf-panel__body">
                <p class="xf-note"><i class="bi bi-info-circle"></i><span>{{ __t('messages.branches.none') }}</span></p>
            </div>
        </section>
    @else
        <div class="xf-branches">
            @foreach ($branches as $branch)
                <section class="xf-branch">
                    <h2 class="xf-branch__name">{{ $branch->name($locale) }}</h2>
                    @if ($address = $branch->address($locale))
                        <p class="xf-branch__row"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $address }}</span></p>
                    @endif
                    @if ($branch->manager)
                        <p class="xf-branch__row"><i class="bi bi-person" aria-hidden="true"></i><span>{{ $branch->manager }}</span></p>
                    @endif
                    @if ($branch->phones)
                        <p class="xf-branch__row xf-branch__phones">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <span>
                                @foreach ($branch->phones as $phone)
                                    <a href="tel:{{ \App\Models\Branch::tel($phone) }}">{{ $phone }}</a>
                                @endforeach
                            </span>
                        </p>
                    @endif
                    @if ($map = $branch->mapUrl())
                        <a class="xf-btn xf-btn--ghost xf-branch__map" href="{{ $map }}" target="_blank" rel="noopener">
                            <i class="bi bi-map"></i> {{ __t('messages.branches.map') }}
                        </a>
                    @endif
                </section>
            @endforeach
        </div>
    @endif

</x-insurence.flow>
@endsection
