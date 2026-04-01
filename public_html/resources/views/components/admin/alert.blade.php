@php
    $variant = $variant ?? 'primary';

    $attributes = $attributes
        ->class([
            'alert',
            "alert-$variant",
            isset($close) ? 'alert-dismissible' : '',
            isset($important) ? 'alert-important' : '',
        ])
        ->except(['variant', 'icon', 'close', 'important', 'message']);
@endphp

<div {{ $attributes }} role="alert">
    <div class="d-flex">
        @isset($icon)
            <i class="{{ $icon }} fs-2 me-1"></i>
        @endisset
        <div class="message">
            {!! $message ?? '' !!}
            {{ $slot ?? '' }}
        </div>
    </div>
    @if (isset($close))
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    @endif
</div>
