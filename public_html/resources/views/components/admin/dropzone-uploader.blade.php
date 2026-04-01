<div class="file-upload-area mb-3">
    @isset($label)
        <h4 class="form-label {{ isset($required) && $required ? 'required' : '' }}">{{ $label}}</h4>
    @endisset
    <div class="dropzone" autocomplete="off" novalidate>
        <div class="fallback">
            <input type="file" {{ $attributes->except(['label']) }} />
        </div>
    </div>
</div>