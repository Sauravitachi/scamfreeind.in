<svg {{ $attributes->merge(['class' => 'icon me-2']) }}>
    <use xlink:href="{{ asset('assets/theme/icons/tabler.sprite.svg#icon-' . $icon) }}"></use>
</svg>