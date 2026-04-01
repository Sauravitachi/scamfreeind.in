@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.users.index'),
    'filters' => true,
    'buttons' => [
        auth()->user()->can(Permission::USER_CREATE->value)
            ? ['label' => 'Add new user', 'icon' => 'ti ti-plus', 'url' => route('admin.users.create')]
            : null,
    ],
])

@section('filters-body')
    <div class="row">

        <div class="col-12">
            <x-admin.select name='filter_role' label='Role' class="filter-select2" :options="$roles->pluck('name', 'id')->toArray()" placeholder="Select" :selected="request()->filter_role" />
        </div>

        <div class="col-12">
            <x-admin.select name='filter_status' label='Status' class="filter-select2" :options="[1 => 'Active', 0 => 'InActive']" placeholder="Select" :selected="request()->filter_status" />
        </div>

        <div class="col-12">
            <x-admin.select name='filter_logged_in' label='Logged In?' class="filter-select2" :options="[1 => 'Yes', 0 => 'No']" placeholder="Select" :selected="request()->filter_logged_in" />
        </div>

    </div>
@endsection

@include('admin.layouts.components.select2')
@include('admin.layouts.components.datatable')

@section('content')
    <div>
        
        @if(auth()->user()->can(Permission::LOGIN_AS_USER) && session()->has('user_login'))
            <x-admin.alert variant='danger' message="'Login as User' is currently disabled, because you already are logged in as other user." />
        @endif

        <div class="row">
            <div class="col-12">
                @include('admin.layouts.components.datatable-header', [
                    'id' => 'users-table',
                    'data' => [
                        ['title' => 'Sr.', 'classname' => 'text-center'],
                        ['title' => 'Username'],
                        ['title' => 'Name'],
                        ['title' => 'Email'],
                        ['title' => 'Logged In?'],
                        ['title' => 'Status'],
                        ['title' => 'Role', 'width' => '20%', 'classname' => 'text-center'],
                        ['title' => 'Sub Admin'],
                        ['title' => 'Registerd At'],
                        ['title' => 'Action'],
                    ],
                ])
            </div>
        </div>

        @include('admin.users._user_details_offcanvas')
        @include('admin.users._change_password_modal')
        @include('admin.users._force_release_freeze_modal')
        @include('admin.users._set_target_modal')

    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;
        
        const pms = @js([
            'user_update' => auth()->user()->can(Permission::USER_UPDATE),
            'sub_admin_assign' => auth()->user()->can(Permission::SUB_ADMIN_MANAGEMENT),
        ]);

        const subAdminUsers = @js($subAdminUsers);


        const Action = {
            ...@js([
                'user_id' => auth()->id(),
                'editUrl' => route('admin.users.edit', ':id'),
                'changeStatusUrl' => route('admin.users.change-status', ':id'),
                'deleteUrl' => route('admin.users.destroy', ':id'),
                'loginAsUserUrl' => route('admin.users.login-as-user', ':id'),
                'updatePreferenceUrl' => route('admin.users.update-preference', ':id'),
                'setTargetUrl' => route('admin.users.set-target', ':id'),
                'canEdit' => auth()->user()->can(Permission::UPDATE_ALL_USERS_DETAILS),
                'canDelete' => auth()->user()->can(Permission::USER_DELETE),
                'canLoginAsUser' => auth()->user()->can(Permission::LOGIN_AS_USER) && !session()->has('user_login'),
                'canSetTarget' => auth()->user()->can(Permission::SUB_ADMIN_MANAGEMENT), // Using this for now
            ]),
            edit: function(id) {
                if (!Action.canEdit)
                    return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                return Action.canDelete ?
                    `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>` :
                    ``;
            },
            loginAsUser: function(id) {
                return Action.canLoginAsUser && (Action.user_id !== id) ?
                    `<a href="javascript:;" data-login-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-login-2 text-warning h1"></i></a>` :
                    ``;
            },
            setTarget: function(id, userType) {
                return Action.canSetTarget && ['sales', 'drafting'].includes(userType) ?
                    `<a href="javascript:;" onclick="SetTargetModule.open(${id})" class="cursor-pointer mx-1"><i class="ti ti-target text-success h1"></i></a>` :
                    ``;
            }
        };

        $(document).ready(function() {
            dtTable = $('#users-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                initComplete: function() {
                    $(this.api().table().container()).find('input').parent().wrap('<form>').parent().attr('autocomplete', 'off');
                },
                ajax: {
                    url: @js(route('admin.users.index')),
                    data: function(d) {
                        d = withFilterData(d);
                    }
                },
                order: [
                    [8, 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                createdRow: function(row, data, dataIndex) {
                    $('td:eq(0)', row).addClass('text-center');
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'username',
                        name: 'username',
                        render: function(data, type, row, meta) {
                            const id = row['id'];
                            const avatar = avatarField(row.profile_avatar, 'me-2')
                            const html =
                                `<div class="d-flex align-items-center">${avatar} ${data}</div>`;
                            const $elem = $(html)
                                .attr('role', 'button')
                                .attr('onclick', `UserDetailModule.open(${id})`)
                                .addClass('text-decoration-underline');
                            return $elem.outerHtml();
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'login_at',
                        name: 'login_at',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return row.is_logged_in ? `<div class="d-flex align-items-center"><i class="fs-1 me-1 ti ti-circle-filled text-success"></i>${data}</div>` : ``;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row, meta) {
                            return badge({
                                title: data ? 'Active' : 'InActive',
                                color: data ? 'green' : 'red',
                                classname: 'user-status-badge',
                                dataId: row['id'],
                                dataValueId: data
                            });
                        }
                    },
                    {
                        data: 'roles',
                        name: 'roles_count',
                        searchable: false,
                        orderable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            let roles = '';
                            data && data.length > 0 && data.forEach((role) => {
                                roles +=
                                    `<span class="badge bg-blue text-blue-fg me-1 mt-1">${role.name}</span>`;
                            });
                            return roles;
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {

                            const isSales = (row.user_type === 'sales') || (row.roles && row.roles.some(role => role.name.toLowerCase().includes('sales')));

                            if (!isSales) {
                                return '';
                            }

                            let options = '<option value="">Select Sub Admin</option>';
                            subAdminUsers.forEach(user => {
                                options += `<option value="${user.id}" ${row.sub_admin_id == user.id ? 'selected' : ''}>${user.name}</option>`;
                            });

                            return `<select class="form-select form-select-sm sub-admin-id-select" data-id="${row.id}" onclick="event.stopPropagation()">
                                        ${options}
                            </select>`;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return Action.loginAsUser(data) + Action.setTarget(data, row.user_type) + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {

                $('[data-delete-id]').on('click', deleteUser);

                $('[data-login-id]').on('click', loginAsUser);

                if(pms.user_update) {
                    $('.user-status-badge[data-id]').on('click', function() {
                        const id = $(this).data('id');
                        const status = $(this).data('value');
                        const url = Action.changeStatusUrl.replace(':id', id);
                        changeResourceStatus({
                            url,
                            body: {
                                status: status ? 0 : 1 // reverted for status change
                            },
                            dtTable,
                            alertMessage: 'You are about to change the status of a user.'
                        });
                    });
                }

                $('.sub-admin-id-select').on('change', function() {
                    const id = $(this).data('id');
                    const value = $(this).val();
                    const url = Action.updatePreferenceUrl.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            key: 'sub_admin_id',
                            value: value
                        },
                        success: function(res) {
                            if (res.success) {
                                toast.open({
                                    type: 'success',
                                    message: 'Sub Admin Assigned!'
                                });
                            }
                        },
                        error: function() {
                            toast.open('error', 'Something Went Wrong!');
                        }
                    });
                });

            });

            FilterModule.registerDatatable(dtTable);


            function deleteUser() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>user</strong>.<br>If you proceed, you won't be able to revert this.`,
                    onConfirm: async function() {
                        const url = Action.deleteUrl.replace(':id', id);
                        await $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    response.toast && toast.open(response.toast);
                                    dtTable.draw(false);
                                }
                            },
                            error: function() {
                                toast.open('error', 'Something Went Wrong!');
                            }
                        });
                    }
                });
            }

            function loginAsUser() {
                const id = $(this).data('login-id');
                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-login-2',
                    message: `You are about to login as other <strong>user</strong>.<br>If you proceed, you will be logged out of your account.`,
                    onConfirm: async function() {
                        const url = Action.loginAsUserUrl.replace(':id', id);
                        await $.ajax({
                            url: url,
                            type: 'POST',
                            success: function(res) {
                                if(res?.redirectTo) {
                                    redirect(res.redirectTo);
                                }
                            },
                            error: function() {
                                toast.open('error', 'Something Went Wrong!');
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush
