@php
    /** @var \App\Models\Scam $scam */
    $current = $scam;
    $ancestors = [];

    // Collect all parents recursively (top-most parent last)
    while ($current) {
        $ancestors[] = $current;
        $current = $current->recycledParentScam()->first(['id', 'track_id', 'recycled_parent_scam_id']); // assuming this is the parent relation
    }

    // Reverse to get top-most parent first
    $ancestors = array_reverse($ancestors);
@endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">Case Lifecycle</div>
        <div class="h3 t-3 timeline_container">
            @php $hasActivities = false; @endphp
            @foreach ($ancestors as $scamItem)
                @if ($scamItem->activities->isNotEmpty())
                    @php $hasActivities = true; @endphp
                    <div class="mb-4">
                        <h4>Activities for Case: #{{ $scamItem->track_id  }}</h4>
                        <ul class="timeline_ul">
                            @foreach ($scamItem->activities as $index => $activity)
                                <li class="timeline_li">
                                    @if ($at = $activity->created_at)
                                        <div class="timeline_time">
                                            {{ format_date($at) }}
                                            @if ($activity->user)
                                                by
                                                <span class="text-primary">
                                                    {{ $activity->user->name_with_username }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    <p class="timeline_p">{{ $activity->description }}</p>
                                </li>
                                @php
                                    $nextActivity = $scamItem->activities[$index + 1] ?? null;
                                @endphp

                                @if ($nextActivity)
                                    <li class="timediff_li">
                                        {{ $nextActivity->created_at->diffForHumans($activity->created_at) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            @unless ($hasActivities)
                <span class="text-secondary">
                    <i class="fs-3 me-2 ti ti-clock-x"></i>
                    No Activity!
                </span>
            @endunless
        </div>
    </div>
</div>
