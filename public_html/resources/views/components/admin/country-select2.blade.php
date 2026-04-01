@use(App\Utilities\Html)

@include('admin.layouts.components.select2')

<div class="select-wrapper mb-3">
    @isset($label)
        <label class="form-label {{ isset($required) ? 'required' : '' }}">
            {{ $label }}
        </label>
    @endisset

    <select {{ $attributes->class(['form-select', 'select2', 'country-select']) }}>
    </select>

    @isset($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endisset
</div>


@pushOnce('script')
    @php
        $selectCountriesData = Html::selectCountriesData();
    @endphp
    <script>
        $(function() {
            window.countries = {!! $selectCountriesData !!};

            const select = $('.country-select');

            select.empty();

            select.append(`<option value="" selected disabled>Select Country</option>`);

            $.each(window.countries, function(key, country) {
                select.append(`<option value="${key}">${country.emoji} ${country.name}</option>`);
            });
        });
    </script>
@endPushOnce

@push('script')
    <script>
        $(document).ready(function() {

            const [selectedId, defaultValue] = @js([$id, $default ?? null]);

            if (defaultValue) {
                $('#' + selectedId).val(defaultValue).trigger('change');
            }
        });
    </script>
@endpush
