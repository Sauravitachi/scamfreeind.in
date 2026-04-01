<div class="select-wrapper mb-3">
    <label class="form-label {{ isset($required) ? 'required' : '' }}">
        {{ $label }}
    </label>
    <select id="{{ $id }}" {{ $attributes->class(['form-select', 'select2-ajax'])->merge([
        'data-route' => $route,
        'data-placeholder' => $placeholder ?? 'search',
        'data-minimum-input-length' => $minimumInputLength ?? 1,
        'data-paginate' => !!($paginate ?? false)
    ])->except(['route', 'placeholder', 'paginate', 'default', 'minimumInputLength', 'dropdownParent']) }}>
        @isset($default)
            <option value="{{ $default['id'] }}" selected>{{ $default['label'] }}</option>
        @endisset
    </select>
    @isset($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endisset
</div>


@push('script')
<script>
    $(document).ready(function() {
        if (typeof initSelect2Ajax === 'function') {
            initSelect2Ajax($('#{{ $id }}'));
        }
    });
</script>
@endpush