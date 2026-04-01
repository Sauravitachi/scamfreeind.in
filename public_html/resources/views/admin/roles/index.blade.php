@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.roles.index'),
    'buttons' => [
        auth()->user()->can(Permission::ROLE_CREATE->value)
            ? ['label' => 'Add new role', 'icon' => 'ti ti-plus', 'url' => route('admin.roles.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'roles-table',
                'data' => [
                    ['title' => 'Name', 'width' => '60%', 'classname' => 'text-start'],
                    ['title' => 'Permissions Granted', 'width' => '15%'],
                    ['title' => 'Action', 'width' => '20%'],
                ],
            ])
        </div>
    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;


        const Action = {
            ...@js([
                'showUrl' => route('admin.roles.show', ':id'),
                'editUrl' => route('admin.roles.edit', ':id'),
                'deleteUrl' => route('admin.roles.destroy', ':id'),
                'canEdit' => auth()->user()->can(Permission::ROLE_UPDATE->value),
                'canDelete' => auth()->user()->can(Permission::ROLE_DELETE->value),
            ]),
            show: function(id) {
                const url = this.showUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
            },
            edit: function(id) {
                if (!Action.canEdit)
                    return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                if (!Action.canDelete)
                    return '';
                return `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>`;
            },
        };

        $(document).ready(function() {
            dtTable = $('#roles-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.roles.index')),
                order: [
                    [0, 'asc']
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            return wrap_anchor(data, Action.showUrl.replace(':id', row.id));
                        }
                    },
                    {
                        data: 'permissions_count',
                        name: 'permissions_count',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return Action.show(data) + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', deleteRole);
            });


            function deleteRole() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete a <strong>role</strong>.<br>If you proceed, you won't be able to revert this.`,
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
        });
    </script>
@endpush
