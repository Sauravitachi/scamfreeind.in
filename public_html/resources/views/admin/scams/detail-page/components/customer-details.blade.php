@php /** @var \App\Models\Scam $scam */ @endphp

@php $userType = auth()->user()->userType(); @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Customer Details
        </div>
        <div class="h3 mt-3">
            @if ($trackId = $customer->track_id)
                <div class="mt-1">
                    <i class="ti ti-id-badge"></i>
                    <span>#{{ $trackId }}</span>
                </div>
            @endif
            @if ($fullName = $customer->full_name)
                <div class="mt-1">
                    <i class="ti ti-user-filled"></i>
                    <span>{{ $fullName }}</span>
                </div>
            @endif
            @if ($phoneNumber = $customer->full_phone_number)
                <div class="mt-1">
                    <i class="ti ti-phone-filled"></i>
                    {{ $phoneNumber }}
                </div>
            @endif
            @if ($email = $customer->email)
                <div class="mt-1">
                    <i class="ti ti-mail-filled"></i>
                    {{ $email }}
                </div>
            @endif
            @if ($country = $customer->country_with_emoji)
                <div class="mt-1">
                    {{ $country }}
                </div>
            @endif
            @if ($userType === 'admin' && $registeredAt = $customer->created_at)
                <div class="mt-1">
                    <i class="ti ti-calendar-plus"></i>
                    {{ format_date($registeredAt) }}
                </div>
            @elseif($userType === 'sales' && $salesAssignedAt = $scam->sales_assigned_at)
                <div class="mt-1">
                    <i class="ti ti-calendar-plus"></i>
                    {{ format_date($salesAssignedAt) }}
                </div>
            @elseif($userType === 'drafting' && $draftingAssignedAt = $scam->drafting_assigned_at)
                <div class="mt-1">
                    <i class="ti ti-calendar-plus"></i>
                    {{ format_date($draftingAssignedAt) }}
                </div>
            @endif
        </div>
    </div>
</div>