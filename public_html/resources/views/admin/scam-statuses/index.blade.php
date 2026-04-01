@use(App\Constants\Permission)
@use(App\Enums\ScamStatusType)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-statuses.index'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_STATUS_TRANSITION_SHOW) ? ['label' => 'Drafting transitions', 'icon' => 'ti ti-clipboard-data', 'variant' => 'danger', 'url' => route('admin.scam-statuses.transition', ScamStatusType::DRAFTING)] : null,
        auth()->user()->can(Permission::SCAM_STATUS_CREATE->value)
            ? ['label' => 'Add new scam status', 'icon' => 'ti ti-plus', 'url' => route('admin.scam-statuses.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'scam-status-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '10%', 'classname' => 'text-center'],
                    ['title' => 'Type', 'width' => '10%'],
                    ['title' => 'Slug', 'width' => '10%'],
                    ['title' => 'Title', 'width' => '25%'],
                    ['title' => 'Notify after days', 'width' => '8%'],
                    ['title' => 'Is File Required?', 'width' => '7%'],
                    ['title' => 'Action', 'width' => '10%'],
                ],
            ])
        </div>
    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;

        const Action = {
            showUrl: @js(route('admin.scam-statuses.show', ':id')),
            editUrl: @js(route('admin.scam-statuses.edit', ':id')),
            deleteUrl: @js(route('admin.scam-statuses.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::SCAM_STATUS_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::SCAM_STATUS_DELETE->value)
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
            dtTable = $('#scam-status-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.scam-statuses.index')),
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
                        data: 'notify_after_days',
                        name: 'notify_after_days',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'is_file_required',
                        name: 'is_file_required',
                        render: function(data, type, row, meta) {
                            return data;
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
                $('[data-delete-id]').on('click', deleteScamStatus);
            });


            function deleteScamStatus() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>scam status</strong>.<br>If you proceed, you won't be able to revert this.`,
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
