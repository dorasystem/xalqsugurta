@extends('layouts.app')
@section('title', __('messages.welcome'))
@section('content')

<?php
$url = 'https://new.xalqsugurta.uz/';
$lang = app()->getLocale();

$buytext = [
    'uz' => 'Buy Policy',
    'ru' => 'Купить страховку',
    'en' => 'Buy Policy',
];

?>

    <link rel="stylesheet" href="{{ asset('assets/css/products.css') }}">

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

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

                @foreach ($products as $product)
                    <div
                        class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex flex-col h-full">

                        <div class="w-16 h-16 rounded-xl flex items-center justify-center mb-5"
                            style="background-color: {{ $product['icon_bg'] }};">
                            <i class="{{ $product['icon'] }} text-2xl" style="color: {{ $product['icon_color'] }};"></i>
                        </div>

                        <div class="flex flex-col flex-1">

                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ $product['name_' . $lang] }} 
                            </h3>

                            <p class="text-sm text-gray-500 mb-6 leading-relaxed flex-1">
                                {{ $product['desc_' . $lang] }}
                            </p>

                            <a href="{{ $url . 'uz/' . $product['route'] }}"
                                class="mt-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium text-white"
                                style="background-color: {{ $product['icon_color'] }};">
                                {{ $buytext[$lang] }}
                                <i class="bi bi-arrow-right"></i>
                            </a>

                        </div>
                    </div>
                @endforeach

            </div>

        </div>
    </section>

@endsection
