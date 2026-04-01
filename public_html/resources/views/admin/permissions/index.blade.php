@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => 'Permissions',
    'breadcrumbs' => Breadcrumbs::render('admin.permissions.index'),
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'permissions-table',
                'data' => [
                    ['title' => 'Sr.', 'width' => '5%'],
                    ['title' => 'Name', 'width' => '30%'],
                    ['title' => 'Label', 'width' => '40%'],
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
            showUrl: @js(route('admin.permissions.show', ':id')),
            editUrl: @js(route('admin.permissions.edit', ':id')),
            canEdit: @js(
                auth()->user()->can(Permission::PERMISSION_UPDATE->value)
            ),
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
        };

        $(document).ready(function() {
            dtTable = $('#permissions-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.permissions.index')),
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
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            return wrap_anchor(data, Action.showUrl.replace(':id', row.id));
                        }
                    },
                    {
                        data: 'label',
                        name: 'label',
                        render: function(data, type, row, meta) {
                            return data;
                            if (data) {
                                return data;
                            } else {
                                return 'N/A';
                            }
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return Action.show(data) + Action.edit(data);
                        }
                    },
                ],
            });
        });
    </script>
@endpush
