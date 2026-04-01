@php($isForceReleased = $user->isFreezeForceReleased())

<div class="card mb-3">
    <div class="card-body">
        <div class="card-title d-flex align-items-center">
            <i class="fs-1 me-2 ti ti-flower text-primary"></i>
            <span>Status Freezes</span>
        </div>
        @if ($user->freeze_disabled_until > now())
            <div class="text-danger mb-2">
                The freeze has been force released until {{ format_date($user->freeze_disabled_until) }}
            </div>
        @endif

        @if ($user->scamStatusFreezes->isNotEmpty())
            <div>
                @foreach ($user->scamStatusFreezes as $freeze)
                    <x-admin.alert variant='info'>
                        Status : <span class="fw-bolder">{{ $freeze->status?->title  ?? 'N/A' }}</span>
                        @if ($freeze->created_at)
                            - freezed at {{ format_date($freeze->created_at) }}
                        @endif
                        <span class="ms-1">
                            - (Total Cases : <span class="fw-bolder">{{ $freeze->scam_count }}</span>)
                        </span>
                        @if($releaseThreshold = $freeze->status?->freeze_release_scams_threshold)
                            @php($neededToResolve = $freeze->scam_count - $releaseThreshold)
                            @if ($neededToResolve > 0)
                                <div class="text-danger mt-2">
                                    <i class="ti ti-circle-filled"></i> Need to resolve {{ $neededToResolve }} cases to unfreeze this status.
                                </div>
                            @endif
                        @endif
                    </x-admin.alert>
                @endforeach
            </div>

            @if (!$isForceReleased)
                <div class="text-end">
                    <x-admin.button label='Force Release Freeze' variant='danger' icon='ti ti-lock-open' onclick="ForceReleaseFreezeModule.open();" />
                </div>
            @endif
        @else
            <span class="text-secondary">N/A</span>
        @endif
    </div>
 </div> 