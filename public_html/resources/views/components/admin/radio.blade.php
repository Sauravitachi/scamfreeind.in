<div class="form-check form-check-inline">
    @php
        $id = $attributes->get('id', 'radio_' . uniqid());
    @endphp

    <input type="radio" id="{{ $id }}" role="button"
        {{ $attributes->class(['form-check-input', isset($name) && !isset($skipErrors) && $errors->has($name) ? 'is-invalid' : ''])->except(['label', 'checked']) }}
        @checked(isset($checked) ? !!$checked : false)>

    @isset($label)
        <label for="{{ $id }}" role="button" class="form-check-label">{{ $label }}</label>
    @endisset

    @if (isset($name) && !isset($skipErrors))
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>
