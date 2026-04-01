@php /** @var \App\Models\Scam $scam */ @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Scam Details
        </div>
        <div class="h3 mt-3">
            @if ($trackId = $scam->track_id)
            <div class="mt-1">
                <i class="ti ti-id-badge"></i>
                <span>#{{ $trackId }}</span>
            </div>
            @endif
            @if ($scamType = $scam->scamType)
            <div class="mt-1">
                <i class="ti ti-campfire"></i>
                <span>{{ $scamType->title }}</span>
            </div>
            @endif
            @if ($scamAmount = $scam->scam_amount)
            <div class="mt-1">
                <i class="ti ti-moneybag"></i>
                <span>{{ format_amount($scamAmount) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>