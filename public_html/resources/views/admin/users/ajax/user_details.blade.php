@use(App\Constants\Permission)

@php
    $canAccessActions = auth()->user()->canAny(Permission::CHANGE_ALL_USERS_PASSWORD);
@endphp

<div>
    <div class="row">
        <div class="col-lg-6">
            @include('admin.users.ajax._basic_info')
            @if (in_array($user->getRoleString(), ['sales', 'drafting']))
                @include('admin.users.ajax._status_freezes')
            @endif
        </div>
        @if($canAccessActions)
            <div class="col-lg-6 d-flex flex-column gap-3">
                @include('admin.users.ajax._actions')
                @can(Permission::VIEW_ALL_USERS_ACTIVITIES)
                    @php($hasAnyActivity = $user->activities->isNotEmpty())
                    @include('admin.users.ajax._activities')
                @endcan()
            </div>
        @endif
    </div>
</div>
