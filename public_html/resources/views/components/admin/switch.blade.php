<label class="form-check form-switch fit-content">
    <input type="checkbox" {{ $checked ?? false ? 'checked' : '' }} {{ $attributes->class('form-check-input cursor-pointer')->except(['checked', 'label']) }} />
    @isset($label)
        <span class="form-check-label cursor-pointer">
            {{ $label }}
        </span>
    @endisset
</label>