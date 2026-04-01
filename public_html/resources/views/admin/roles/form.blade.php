@use(App\Http\Requests\Admin\RoleRequest)
@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php /** @var \App\Models\Role $role */ @endphp

@php
    $role ??= null;
    $isUpdate = !!$role;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="{{ $method }}" id="role-form">
    @csrf
    @if ($isUpdate)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-12">
            <x-admin.input name='name' label='Role Name' placeholder='Enter role name' :value="$role?->name" required />
        </div>
        <div class="rol-12 my-3">
            <input type="hidden" name="is_admin" value="0">
            <x-admin.checkbox name='is_admin' label='Is Admin?' value="1" :checked="$role?->is_admin" />
        </div>
        <div class="col-12">
            <h4 class="form-label required">
                Permissions
            </h4>
            @error('permissions')
                <h5 class="text-danger">
                    {{ $message }}
                </h5>
            @enderror
        </div>

        <div class="col-12">
            <x-admin.checkbox label='All' id="all-permissions-check" />
        </div>

        @foreach ($permissions as $permission)
            <div class="col-lg-3 col-md-4 col-6 mt-2">
                <x-admin.checkbox class="permission" name='permissions[]{{ $permission->name }}' :label="$permission->label"
                    :value="$permission->name" :checked="isset($rolePermissions[$permission->id])" skip-errors />
            </div>
        @endforeach

        <div class="col-12 mt-3">
            <div>
                <label class="form-label">
                    Allowed roles for creating users
                </label>
                <x-admin.checkbox label="Select All" id="user_creatable_roles_select_all_chk" />
                <x-admin.select name="user_creatable_roles[]" class="select2" id="user_creatable_roles_select" :options="$roles->pluck('name', 'id')->toArray()" multiple />
            </div>
        </div>

        <div class="col-12">
            @if ($isUpdate)
                <x-admin.button class="float-end btn-block" label='Update' icon='ti ti-device-floppy' submit />
            @else
                <x-admin.button class="float-end btn-block" label='Create' icon='ti ti-plus' submit />
            @endif
        </div>
    </div>

</form>

@push('script')
    {!! JsValidatorFacade::formRequest(RoleRequest::class, '#role-form') !!}
    <script>

        const userCreatableRoles = @json($role?->user_creatable_roles ?? []);

        $(document).ready(function() {
            const $allPermissionsCheck = $('#all-permissions-check');
            const $permissions = $('.permission');

            $allPermissionsCheck.on('click', function() {
                $permissions.prop('checked', $allPermissionsCheck.is(':checked'));
            });

            $permissions.on('change', function() {
                if (!$(this).is(':checked')) {
                    $allPermissionsCheck.prop('checked', false);
                } else if ($permissions.length === $permissions.filter(':checked').length) {
                    $allPermissionsCheck.prop('checked', true);
                }
            });

            $permissions.trigger('change');

            ajaxForm('#role-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });
        });

        $(document).ready(function() {
            const selectSelector = "#user_creatable_roles_select";
            const checkboxSelector = "#user_creatable_roles_select_all_chk";

            // Select/deselect all on checkbox change
            $(checkboxSelector).on('change', function() {
                if ($(this).is(':checked')) {
                    let allValues = $(`${selectSelector} option`).map(function() {
                        return $(this).val();
                    }).get();
                    $(selectSelector).val(allValues).trigger('change');
                } else {
                    $(selectSelector).val(null).trigger('change');
                }
            });

            // Watch for manual select2 changes
            $(selectSelector).on('change', function() {
                const totalOptions = $(`${selectSelector} option`).length;
                const selectedOptions = $(this).val() ? $(this).val().length : 0;

                // If all are selected, check the checkbox, else uncheck
                $(checkboxSelector).prop('checked', selectedOptions === totalOptions);
            }).val(userCreatableRoles).trigger('change');
        });

    </script>
@endpush
