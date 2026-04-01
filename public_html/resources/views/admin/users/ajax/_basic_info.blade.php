<div class="card mb-3">
    <div class="card-body">
        <div class="card-title">Basic info</div>
        <div class="mb-2">
            <i class="ti ti-user"></i>
            <strong>{{ $user->name }} ({{ $user->username }})</strong>
        </div>
        <div class="mb-2">
            <i class="ti ti-mail"></i>
            <strong>{{ $user->email }}</strong>
        </div>
        <div class="mb-2">
            <i class="ti ti-phone"></i>
            <strong>{{ $user->full_phone_number }}</strong>
        </div>
        @if ($country = $user->country_with_emoji)
            <div class="mb-2">
                <strong> {{ $country }}</strong>
            </div>
        @endif
    </div>
</div>