@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-sources.index'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_SOURCE_CREATE->value)
            ? ['label' => 'Add new source', 'icon' => 'ti ti-plus', 'url' => route('admin.scam-sources.create')]
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
                    ['title' => 'Slug'],
                    ['title' => 'Title'],
                    ['title' => 'Indicator Color'],
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
            showUrl: @js(route('admin.scam-sources.show', ':id')),
            editUrl: @js(route('admin.scam-sources.edit', ':id')),
            deleteUrl: @js(route('admin.scam-sources.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::SCAM_SOURCE_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::SCAM_SOURCE_DELETE->value)
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
                ajax: @js(route('admin.scam-sources.index')),
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
                        data: 'indicator_color',
                        name: 'indicator_color',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if(data) {
                                return `<button class="btn" style="background-color: ${data}; width: 60px"></button>`;
                            }
                            return noContentText();
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
                $('[data-delete-id]').on('click', deleteScamSource);
            });


            function deleteScamSource() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>scam source</strong>.<br>If you proceed, you won't be able to revert this.`,
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
