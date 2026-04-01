@php /** @var \App\Models\Scam $scam */ @endphp

@php
    $statusRecords = $scam->statusRecords->where('status_type', $statusType);
    if($causer) {
        $statusRecords = $statusRecords->where('causer_id', $causer->id);
    }
@endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Case {{ ucfirst($statusType->value) }} Status Lifecycle
        </div>
        <div class="h3 t-3">
            @if ($statusRecords->isNotEmpty())
                <div class="timeline_container">
                    <ul class="timeline_ul">
                        @foreach ($statusRecords as $statusRecord)
                            <li class="timeline_li">
                                @if ($at = $statusRecord->created_at)
                                    <div class="timeline_time">
                                        {{ format_date($at) }}
                                        @if ($statusRecord->causer)
                                            by
                                            <span class="text-primary">
                                                {{ $statusRecord->causer->name_with_username }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                <p class="timeline_p">
                                    @if ($statusRecord->status)
                                        <span class="text-muted">Updated status to :</span> {{ $statusRecord->status->title }}
                                    @else
                                        Removed status
                                    @endif
                                </p>
                                @if ($statusRecord->status_remark)
                                    <p class="text-muted fw-normal">
                                    <span class="text-dark">Remark: </span> {{ $statusRecord->status_remark }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <span class="text-secondary">
                    <i class=" fs-3 me-2 ti ti-clock-x"></i>
                    No Activity!
                </span>
            @endif
        </div>
    </div>
</div>