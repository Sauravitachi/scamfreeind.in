@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.specializations.index'),
    'buttons' => [
        auth()->user()->can(Permission::SPECIALIZATION_CREATE->value)
            ? ['label' => 'Add new specialization', 'icon' => 'ti ti-plus', 'url' => route('admin.specializations.create')]
            : null,
    ],
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'specializations-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '5%', 'classname' => 'text-center'],
                    ['title' => 'Slug', 'width' => '35%'],
                    ['title' => 'Title', 'width' => '40%'],
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
            showUrl: @js(route('admin.specializations.show', ':id')),
            editUrl: @js(route('admin.specializations.edit', ':id')),
            deleteUrl: @js(route('admin.specializations.destroy', ':id')),
            canShow: @js(auth()->user()->can(Permission::SPECIALIZATION_LIST->value)),
            canEdit: @js(auth()->user()->can(Permission::SPECIALIZATION_UPDATE->value)),
            canDelete: @js(auth()->user()->can(Permission::SPECIALIZATION_DELETE->value)),
            
            show: function(id) {
                if (!Action.canShow) return '';
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
            dtTable = $('#specializations-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.specializations.index')),
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
                        data: 'slug',
                        name: 'slug',
                        render: function(data, type, row, meta) {
                            let html = `<code>${data}</code>`;
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
                            return `<strong>${data}</strong>`;
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
                $('[data-delete-id]').on('click', deleteSpecialization);
            });

            function deleteSpecialization() {
                const id = $(this).data('delete-id');
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete the <strong>specialization</strong>.<br>If you proceed, you won't be able to revert this.`,
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
