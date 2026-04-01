<div class="card border-left-{{ $variant ?? 'primary' }} shadow h-100">
    <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
                <div class="fs-3 font-weight-bold text-{{ $variant ?? 'primary' }} text-uppercase mb-1">
                    {{ $title }}
                </div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">
                    {!! $value !!}
                </div>
            </div>
            @isset($icon)
                <div class="col-auto">
                    <i class="{{ $icon }} fs-1 text-secondary"></i>
                </div>
            @endisset
        </div>
    </div>
</div>
