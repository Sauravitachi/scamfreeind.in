@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-types.index'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_TYPE_CREATE->value)
            ? ['label' => 'Add new scam type', 'icon' => 'ti ti-plus', 'url' => route('admin.scam-types.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'scam-type-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '5%', 'classname' => 'text-center'],
                    ['title' => 'Slug', 'width' => '30%'],
                    ['title' => 'Title', 'width' => '30%'],
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
            editUrl: @js(route('admin.scam-types.edit', ':id')),
            deleteUrl: @js(route('admin.scam-types.destroy', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::SCAM_TYPE_UPDATE->value)
            ),
            canDelete: @js(
                auth()->user()->can(Permission::SCAM_TYPE_DELETE->value)
            ),
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
            dtTable = $('#scam-type-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.scam-types.index')),
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
                            let html = data;
                            if (row['is_default']) {
                                html += badge({
                                    title: 'Default',
                                    classname: 'ms-1 bg-primary'
                                });
                            }
                            return html;
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
                        render: function(data, type, row, meta) {
                            return Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', deleteScamType);
            });


            function deleteScamType() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>scam type</strong>.<br>If you proceed, you won't be able to revert this.`,
                    onConfirm: async function() {
                        const url = Action.deleteUrl.replace(':id', id);
                        await runAjax({
                            url: url,
                            method: 'DELETE',
                            handleToast: true,
                            success: function(response) {
                                dtTable.draw(false);
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush
