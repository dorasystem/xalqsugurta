@extends('layouts.app')
@section('title', __('messages.welcome'))
@section('content')

@php
    $lang = app()->getLocale();
@endphp

    <link rel="stylesheet" href="{{ assetVersioned('assets/css/products.css') }}">

    <section class="home-products">
        <div class="home-products__wrapper">

            {{-- Section header --}}
            <div class="home-products__header">
                <div class="home-products__badge">
                    <i class="bi bi-shield-check-fill"></i>
                    {{ __('messages.insurance') }}
                </div>
                <h2 class="home-products__title">
                    {{ __('messages.products_title') }}
                </h2>
                <p class="home-products__subtitle">
                    {{ __('messages.products_subtitle') }}
                </p>
            </div>

            <div class="home-products__grid">

                @foreach ($products as $product)
                    <a href="{{ $product->url() }}" class="product-card">

                        <div class="product-card__top">
                            <span class="product-card__icon">
                                <i class="{{ $product->icon }}"></i>
                            </span>

                            @if ($product->categoryKey())
                                <span class="product-card__category">
                                    {{ __t('messages.product_categories.' . $product->categoryKey()) }}
                                </span>
                            @endif
                        </div>

                        <h3 class="product-card__title">
                            {{ $product->{'name_' . $lang} }}
                        </h3>

                        <p class="product-card__desc">
                            {{ $product->{'desc_' . $lang} }}
                        </p>

                        <span class="product-card__cta">
                            {{ __t('messages.buy_policy') }}
                            <span class="product-card__arrow">
                                <i class="bi bi-arrow-right"></i>
                            </span>
                        </span>

                    </a>
                @endforeach

            </div>

        </div>
    </section>

@endsection
