<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
        <span class="avatar avatar-sm avatar-rounded"
            style="background-image: url({{ auth()->user()->profile_avatar }})"></span>
        <div class="d-none d-xl-block ps-2">
            <div>
                {{ auth()->user()->name }}
            </div>
            @if ($primaryRole = auth()->user()->primaryRole)
                <div class="mt-1 small text-secondary">{{ $primaryRole->name }}</div>
            @endif
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <a href="#" class="dropdown-item">Status</a>
        <a href="{{ route('admin.profile.index') }}" class="dropdown-item">Profile</a>
        <a href="#" class="dropdown-item">Feedback</a>
        <div class="dropdown-divider"></div>
        <a href="{{ route('admin.account-settings.index') }}" class="dropdown-item">Account Settings</a>
        <a href="javascript:;" onclick="$('#logoutPost').submit();" class="dropdown-item">Logout</a>
    </div>
</div>
