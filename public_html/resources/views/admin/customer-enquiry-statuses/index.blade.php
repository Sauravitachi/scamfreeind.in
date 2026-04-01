@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.customer-enquiry-statuses.index'),
    'buttons' => [
        auth()->user()->can(Permission::CUSTOMER_ENQUIRY_STATUS_CREATE->value)
            ? ['label' => 'Add new status', 'icon' => 'ti ti-plus', 'url' => route('admin.customer-enquiry-statuses.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'customer-enquiry-status-table',
                'data' => [
                    ['title' => 'Sr.', 'classname' => 'text-center'],
                    ['title' => 'Type'],
                    ['title' => 'Slug'],
                    ['title' => 'Title'],
                    ['title' => 'Stats'],
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
            showUrl: @js(route('admin.customer-enquiry-statuses.show', ':id')),
            editUrl: @js(route('admin.customer-enquiry-statuses.edit', ':id')),
            deleteUrl: @js(route('admin.customer-enquiry-statuses.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::CUSTOMER_ENQUIRY_STATUS_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::CUSTOMER_ENQUIRY_STATUS_DELETE->value)
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
            dtTable = $('#customer-enquiry-status-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.customer-enquiry-statuses.index')),
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
                        data: 'type',
                        name: 'type',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'slug',
                        name: 'slug',
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
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            let html = '';

                            if(row.is_remark_required) 
                                html += `<i class="fs-1 ti ti-note text-primary ms-1" title="Remark Required!"></i>`;

                            if(row.consider_resolved) 
                                html += `<i class="fs-1 ti ti-circle-check text-success ms-1" title="Consider Resolved!"></i>`;

                            return html;
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return Action.show(data) + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', deleteCustomerEnquiryStatus);
            });


            function deleteCustomerEnquiryStatus() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>status</strong>.<br>If you proceed, you won't be able to revert this.`,
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
