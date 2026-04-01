@php /** @var \App\Models\Scam $scam */ @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Case Lifecycle
        </div>
        <div class="h3 t-3">
            @if ($scam->activities->isNotEmpty())
                <div class="timeline_container">
                    <ul class="timeline_ul">
                        @foreach ($scam->activities as $activity)
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
                                <p class="timeline_p">
                                    {{ $activity->description }}
                                </p>
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