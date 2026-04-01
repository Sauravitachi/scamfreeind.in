<div class="card card-sm">
    <div class="card-body">
        <div class="row align-items-center">
            @isset($icon)
                <div class="col-auto">
                    <span class="{{ $colorClass ?? 'bg-primary' }} text-white avatar">
                        <i class="{{ $icon }}"></i>
                    </span>
                </div>
            @endisset
            <div class="col">
                @isset($value)
                    <div class="font-weight-medium">{{ $value }}</div>
                @endisset
                @isset($label)
                    <div class="text-secondary">{{ $label }}</div>
                @endisset
            </div>
        </div>
    </div>
</div>