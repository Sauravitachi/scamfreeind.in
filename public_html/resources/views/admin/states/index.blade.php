@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.states.index'),
    'buttons' => [
        auth()->user()->can(Permission::STATE_CREATE->value)
            ? ['label' => 'Add new state', 'icon' => 'ti ti-plus', 'url' => route('admin.states.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'state-table',
                'data' => [
                    ['title' => 'Sr.', 'classname' => 'text-center'],
                    ['title' => 'Name'],
                    ['title' => 'Code'],
                    ['title' => 'Status', 'classname' => 'text-center'],
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
            editUrl: @js(route('admin.states.edit', ':id')),
            deleteUrl: @js(route('admin.states.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::STATE_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::STATE_DELETE->value)
            ),
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
            dtTable = $('#state-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.states.index')),
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'code',
                        name: 'code',
                        render: function(data) {
                            return data || noContentText();
                        }
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center',
                        render: function(data) {
                            if (data) {
                                return '<span class="badge bg-label-success">Active</span>';
                            }
                            return '<span class="badge bg-label-danger">Inactive</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', deleteState);
            });


            function deleteState() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>state</strong>.<br>If you proceed, you won't be able to revert this.`,
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
