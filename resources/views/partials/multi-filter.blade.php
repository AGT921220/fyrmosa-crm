@php
    $raw = $selected ?? [];
    $picked = array_values(array_filter(
        array_map(
            static fn ($value): string => is_scalar($value) ? trim((string) $value) : '',
            is_array($raw) ? $raw : [$raw],
        ),
        static fn (string $value): bool => $value !== '' && $value !== 'all',
    ));
@endphp
<div class="col-md-{{ $col ?? 2 }}">
    <label class="form-label mb-1">{{ $label }}</label>
    <select class="form-select js-multi-filter" name="{{ $name }}[]" multiple data-placeholder="Todos">
        <option value="all" @selected($picked === [])>Todos</option>
        @foreach ($options as $option)
            <option value="{{ $option['value'] }}" @selected(in_array((string) $option['value'], $picked, true))>{{ $option['label'] }}</option>
        @endforeach
    </select>
</div>
@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css">
        <style>
            .ts-wrapper.form-select {
                height: auto;
                padding: 0;
                background-color: #fff;
                background-image: none;
            }

            .ts-wrapper.form-select.focus {
                border-color: #90b5e2;
                box-shadow: 0 0 0 0.25rem rgba(32, 107, 196, 0.25);
            }

            .ts-wrapper.form-select .ts-control {
                border: 0;
                box-shadow: none;
                background: transparent;
                min-height: calc(1.4285714286em + 0.875rem);
                padding: 0.25rem 0.5rem;
            }

            .ts-wrapper.multi .ts-control {
                max-height: 6.5rem;
                overflow-y: auto;
            }

            .ts-wrapper {
                width: 100%;
            }

            .ts-dropdown {
                z-index: 1080;
            }

            .ts-dropdown .ts-dropdown-content {
                max-height: 16rem;
            }

            .ts-dropdown .option {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
        <script src="{{ asset('js/multi-filter.js') }}?v={{ filemtime(public_path('js/multi-filter.js')) }}"></script>
    @endpush
@endonce
