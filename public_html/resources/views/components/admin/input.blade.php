<div class="mb-3">
    @isset($label)
        <label class="form-label {{ isset($required) && $required ? 'required' : '' }}">{{ $label }}</label>
    @endisset
    <input {{ $attributes->class(['form-control', isset($name) && $errors->has($name) ? 'is-invalid' : '']) }} />
    @isset($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endisset
</div>
