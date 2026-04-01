<label class="form-check cursor-pointer fit-content">
    <input type="checkbox"
        {{ $attributes->class(['form-check-input cursor-pointer', isset($name) && !isset($skipErrors) && $errors->has($name) ? 'is-invalid' : '']) }}
        @checked(isset($checked) ? !!$checked : false)>
    @isset($label)
        <span class="form-check-label">{{ $label }}</span>
    @endisset
    @if (isset($name) && !isset($skipErrors))
        @error($name)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    @endif
</label>
