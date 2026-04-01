<div class="mb-3">
    @isset($label)
        <label class="form-label {{ isset($required) ? 'required' : '' }}">{{ $label }}</label>
    @endisset
    <textarea {{ $attributes->class(['form-control']) }}>{{ $value ?? '' }}</textarea>
    @isset($name)
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endisset
</div>
