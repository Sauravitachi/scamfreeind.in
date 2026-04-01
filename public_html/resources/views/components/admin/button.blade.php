@php
    $variant = $variant ?? 'primary';
    $attributes = $attributes->class(['btn p-2', "btn-$variant"])->except(['submit', 'variant', 'icon', 'label']);
@endphp

<button type="{{ isset($submit) ? 'submit' : 'button' }}" {{ $attributes }}>
    @isset($icon)
        <i class="{{ $icon }} fs-2 me-2"></i>
    @endisset
    {{ $label ?? '' }}
</button>
