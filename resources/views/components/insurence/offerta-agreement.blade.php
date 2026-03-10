@props(['product' => null])

@php
    $locale     = getCurrentLocale();
    $field      = 'offerta_' . $locale;
    $offertaPath = $product?->$field;
    $offertaUrl  = $offertaPath ? \Illuminate\Support\Facades\Storage::url($offertaPath) : null;
@endphp

<div class="px-5 pb-5">
    <div class="rounded-xl border @error('offerta_agreed') border-red-300 bg-red-50 @else border-gray-200 bg-gray-50 @enderror p-4">
        <div class="d-flex align-items-start gap-3">
            <input
                type="checkbox"
                id="offerta_agreed"
                name="offerta_agreed"
                value="1"
                class="mt-1 flex-shrink-0"
                style="width:17px;height:17px;cursor:pointer;accent-color:#2563eb;"
                {{ old('offerta_agreed') ? 'checked' : '' }}
            >
            <label for="offerta_agreed" class="text-sm text-gray-700 mb-0" style="cursor:pointer;line-height:1.5;">
                @if ($offertaUrl)
                    {!! __('messages.offerta_agree_with_link', [
                        'link' => '<a href="' . e($offertaUrl) . '" target="_blank" class="text-blue-600 font-semibold hover:underline">' . __('messages.offerta_link_text') . '</a>'
                    ]) !!}
                @else
                    {{ __('messages.offerta_agree') }}
                @endif
                <span class="text-red-500 ms-1">*</span>
            </label>
        </div>
        @error('offerta_agreed')
            <p class="mt-2 text-xs text-red-500 d-flex align-items-center gap-1 mb-0">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
            </p>
        @enderror
    </div>
</div>
