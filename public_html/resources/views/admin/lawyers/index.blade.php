@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.lawyers.index'),
    'buttons' => [
        auth()->user()->can(Permission::LAWYER_CREATE->value)
            ? ['label' => 'Add new lawyer', 'icon' => 'ti ti-plus', 'url' => route('admin.lawyers.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'lawyers-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '5%', 'classname' => 'text-center'],
                    ['title' => 'Name', 'width' => '25%'],
                    ['title' => 'Email', 'width' => '25%'],
                    ['title' => 'Phone', 'width' => '15%'],
                    ['title' => 'Specializations', 'width' => '20%'],
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
            showUrl: @js(route('admin.lawyers.show', ':id')),
            editUrl: @js(route('admin.lawyers.edit', ':id')),
            deleteUrl: @js(route('admin.lawyers.destroy', ':id')),
            canEdit: @js(auth()->user()->can(Permission::LAWYER_UPDATE->value)),
            canDelete: @js(auth()->user()->can(Permission::LAWYER_DELETE->value)),
            
            show: function(id) {
                const url = this.showUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-success h1"></i></a>`;
            },
            edit: function(id) {
                if (!Action.canEdit) return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                if (!Action.canDelete) return '';
                return `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>`;
            },
        };

        $(document).ready(function() {
            dtTable = $('#lawyers-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.lawyers.index')),
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            let html = `<strong>${data}</strong>`;
                            if (!row['is_active']) {
                                html += badge({
                                    title: 'Inactive',
                                    classname: 'ms-1 bg-danger'
                                });
                            } else {
                                html += badge({
                                    title: 'Active',
                                    classname: 'ms-1 bg-success'
                                });
                            }
                            return html;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data, type, row, meta) {
                            return data ? data : '<span class="text-muted">N/A</span>';
                        }
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: function(data, type, row, meta) {
                            return data ? data : '<span class="text-muted">N/A</span>';
                        }
                    },
                    {
                        data: 'specializations',
                        name: 'specializations',
                        orderable: false,
                        render: function(data, type, row, meta) {
                            if (!data || data.length === 0) {
                                return '<span class="text-muted">None</span>';
                            }
                            return data.map(function(spec) {
                                return badge({
                                    title: spec.title,
                                    classname: 'bg-primary me-1 mb-1'
                                });
                            }).join('');
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return Action.show(data) + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', deleteLawyer);
            });

            function deleteLawyer() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>lawyer</strong>.<br>If you proceed, you won't be able to revert this.`,
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
