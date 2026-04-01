<div class="card">
    <div class="card-body">
        <div class="card-title">
            User Activity
            @if ($hasAnyActivity)
                <a href="{{ route('admin.user-activities.index') ."?user_id={$user->id}" }}">
                    <span class="badge bg-blue text-white">See All</span>
                </a>
            @endif
        </div>
        <div class="h3 t-3">
            @if ($hasAnyActivity)
                <div class="timeline_container">
                    <ul class="timeline_ul">
                        @foreach ($user->activities->reverse() as $activity)
                            <li class="timeline_li">
                                @if ($at = $activity->created_at)
                                    <div class="timeline_time">
                                        {{ format_date($at) }}
                                        @if ($activity->causer)
                                            by
                                            <span class="text-primary">
                                                {{ $activity->causer->name_with_username }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                <p class="timeline_p">
                                    {{ '(' . ucwords($activity->event) . ') ' . ucwords($activity->description) }}
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