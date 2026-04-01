@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-registration-amounts.index'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_REGISTRATION_AMOUNT_CREATE->value)
            ? ['label' => 'Add new amount', 'icon' => 'ti ti-plus', 'url' => route('admin.scam-registration-amounts.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'scam-source-table',
                'data' => [
                    ['title' => 'Sr.', 'classname' => 'text-center'],
                    ['title' => 'Title'],
                    ['title' => 'Amount'],
                    ['title' => 'Points'],
                    ['title' => 'Is Active?'],
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
            showUrl: @js(route('admin.scam-registration-amounts.show', ':id')),
            editUrl: @js(route('admin.scam-registration-amounts.edit', ':id')),
            changeStatusUrl: @js(route('admin.scam-registration-amounts.change-status', ':id')),
            deleteUrl: @js(route('admin.scam-registration-amounts.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::SCAM_REGISTRATION_AMOUNT_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::SCAM_REGISTRATION_AMOUNT_DELETE->value)
            ),
            show: function(id) {
                const url = this.showUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
            },
            edit: function(username) {
                if (!Action.canEdit)
                    return '';
                const url = this.editUrl.replace(':id', username);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(username) {
                if (!Action.canDelete)
                    return '';
                return `<a href="javascript:;" data-delete-id="${username}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>`;
            },
        };

        $(document).ready(function() {
            dtTable = $('#scam-source-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.scam-registration-amounts.index')),
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'title',
                        name: 'title',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'points',
                        name: 'points',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data, type, row, meta) {
                            return badge({
                                title: data ? 'Active' : 'InActive',
                                color: data ? 'green' : 'red',
                                classname: 'is-active-btn',
                                dataId: row.id,
                                dataValueId: data
                            });
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return Action.show(data) + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {

                $('[data-delete-id]').on('click', deleteScamRegistrationAmount);

                @can(Permission::SCAM_REGISTRATION_AMOUNT_UPDATE)
                    $('.is-active-btn[data-id]').on('click', function() {
                        const id = $(this).data('id');
                        const status = $(this).data('value');
                        const url = Action.changeStatusUrl.replace(':id', id);
                        changeResourceStatus({
                            url,
                            body: {
                                is_active: status ? 0 : 1 // reverted for status change
                            },
                            dtTable,
                            alertMessage: 'You are about to change the status of a registration amount.'
                        });
                    });
                @endcan
                
            });


            function deleteScamRegistrationAmount() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>amount</strong>.<br>If you proceed, you won't be able to revert this.`,
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
