@props(['product' => null])

@php
    $locale      = getCurrentLocale();
    $field       = 'offerta_' . $locale;
    $offertaPath = $product?->$field;
    $offertaUrl  = $offertaPath ? \Illuminate\Support\Facades\Storage::url($offertaPath) : null;
@endphp

<div id="confirmation" class="card {{ old('policy_start_date') ? '' : 'd-none' }}">
    <div class="card-body">
        <div id="note" class="card-footer bg-light mb-2 {{ old('driver_limit') == 'limited' ? '' : 'd-none' }}">
            <div class="text-danger fw-bold">@lang('insurance.osago.note')</div>
            <span>@lang('insurance.osago.note_text')</span>
        </div>
        <form action="{{ route('osago.storage', ['locale' => getCurrentLocale()]) }}" method="POST" id="storage-form">
            @csrf

            {{-- Offerta rozilik --}}
            <div class="mb-3">
                <div class="rounded-xl border @error('offerta_agreed') border-red-300 bg-red-50 @else border-gray-200 bg-gray-50 @enderror p-4">
                    <div class="d-flex align-items-start gap-3">
                        <input
                            type="checkbox"
                            id="offerta_agreed_osago"
                            name="offerta_agreed"
                            value="1"
                            class="mt-1 flex-shrink-0"
                            style="width:17px;height:17px;cursor:pointer;accent-color:#2563eb;"
                            {{ old('offerta_agreed') ? 'checked' : '' }}
                        >
                        <label for="offerta_agreed_osago" class="text-sm text-gray-700 mb-0" style="cursor:pointer;line-height:1.5;">
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

            <div class="d-flex justify-content-end align-items-center mt-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-check me-2"></i>
                    {{ __('insurance.confirmation.confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>
