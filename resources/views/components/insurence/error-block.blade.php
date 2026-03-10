@props(['id' => 'validation-errors-alert'])

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="{{ $id }}">
        <h4 class="alert-heading">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ __('messages.error') }}
        </h4>
        <p class="mb-2">{{ __('messages.validation_errors_found') }}</p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alertEl = document.getElementById('{{ $id }}');
                if (alertEl) {
                    setTimeout(function() {
                        alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const firstError = document.querySelector('.is-invalid');
                        if (firstError) {
                            setTimeout(function() {
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstError.focus();
                            }, 300);
                        }
                    }, 100);
                }
            });
        </script>
    @endonce
@endif
