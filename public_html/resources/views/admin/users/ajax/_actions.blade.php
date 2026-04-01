@use(App\Constants\Permission)

<div class="card">
    <div class="card-body">
        <div class="card-title">Actions</div>
        @can(Permission::CHANGE_ALL_USERS_PASSWORD)
            <x-admin.button label='Change Account Password' icon='ti ti-key' onclick="ChangeUserPasswordModule.open();" />
        @endcan
    </div>
</div>