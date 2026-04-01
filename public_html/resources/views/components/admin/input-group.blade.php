<div class="mb-3">
    @isset($label)
        <label class="form-label {{ isset($required) ? 'required' : '' }}">{{ $label }}</label>
    @endisset
    <div class="input-icon">
        <span class="input-icon-addon">
            @if(isset($icon))
                <i class="{{ $icon }}"></i>
            @elseif(isset($iconText))
                {{ $iconText }}
            @endif
        </span>
        <input {{ $attributes->class(['form-control', isset($name) && $errors->has($name) ? 'is-invalid' : ''])->except(['label', 'icon']) }} />
        @isset($name)
            @error($name)
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        @endisset
    </div>
</div>
