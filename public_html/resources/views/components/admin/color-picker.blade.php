@php
    $fieldId = $id . '-cp-field';
@endphp

<div>
    @isset($label)
        <div class="form-label {{ isset($required) ? 'required' : '' }}">{{ $label }}</div>
    @endisset
    <div class="custom-pickr-trigger" id="{{ $fieldId }}"></div>
    <input type="hidden" {{ $attributes }}>
</div>

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const defaultColor = @js($default ?? null);

            const obj = {};
            
            defaultColor && (obj.default = defaultColor);

            const pickr = Pickr.create({
                el: "#{{ $fieldId }}",
                theme: 'classic',

                components: {
                    // Main components
                    preview: true,
                    opacity: true,
                    hue: true,

                    // Input/output Options
                    interaction: {
                        hex: true,
                        rgba: true,
                        input: true,
                        save: true
                    }
                },
                ...obj
            });

            // When user clicks "save"
            pickr.on('save', (color) => {
                const hexColor = color.toHEXA().toString();
                document.getElementById('{{ $id }}').value = hexColor;
                pickr.hide(); // optional
            });
        });
    </script>  
@endpush