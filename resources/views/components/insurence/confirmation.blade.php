<div id="confirmation" class="card {{ old('policy_start_date') ? '' : 'd-none' }}">
    <div class="card-body">
        <div id="note" class="card-footer bg-light mb-2 {{ old('driver_limit') == 'limited' ? '' : 'd-none' }}">
            <div class="text-danger fw-bold">@lang('insurance.osago.note')</div>
            <span>@lang('insurance.osago.note_text')</span>
        </div>
        <form action="{{ route('osago.storage', ['locale' => getCurrentLocale()]) }}" method="POST" id="storage-form">
            @csrf
            <div class="d-flex justify-content-end align-items-center mt-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-check me-2"></i>
                    {{ __('insurance.confirmation.confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>
