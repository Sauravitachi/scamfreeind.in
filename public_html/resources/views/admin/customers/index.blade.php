@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.customers.index'),
    'buttons' => [
        auth()->user()->can(Permission::CUSTOMER_CREATE->value)
            ? ['label' => 'Add new customer', 'icon' => 'ti ti-plus', 'url' => route('admin.customers.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'users-table',
                'data' => [
                    ['title' => 'Track Id', 'classname' => 'text-center'],
                    ['title' => 'Full Name'],
                    ['title' => 'Email'],
                    ['title' => 'Country'],
                    ['title' => 'Phone Number'],
                    ['title' => 'Registerd At'],
                    ['title' => 'Action'],
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
                'showUrl' => route('admin.customers.show', ':id'),
                'editUrl' => route('admin.customers.edit', ':id'),
                'deleteUrl' => route('admin.customers.destroy', ':id'),
                'canEdit' => auth()->user()->can(Permission::CUSTOMER_UPDATE->value),
                'canDelete' => auth()->user()->can(Permission::CUSTOMER_DELETE->value),
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
                return Action.canDelete ?
                    `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>` :
                    '';
            },
        };

        $(document).ready(function() {
            dtTable = $('#users-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.customers.index')),
                order: [
                    [5, 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                createdRow: function(row, data, dataIndex) {
                    $('td:eq(0)', row).addClass('text-center');
                },
                columns: [{
                        data: 'track_id',
                        name: 'track_id',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'full_name',
                        name: 'full_name',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'country_name',
                        name: 'country_name',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'phone_number',
                        name: 'phone_number',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            });

            $("#users-table").on('click', '[data-delete-id]', deleteCustomer);


            function deleteCustomer() {
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
        });
    </script>
@endpush
