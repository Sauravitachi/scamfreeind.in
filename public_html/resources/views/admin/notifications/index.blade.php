@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => 'Notifications',
    'breadcrumbs' => Breadcrumbs::render('admin.notifications.index'),
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'notifications-table',
                'data' => [
                    ['title' => 'Notification', 'width' => '80%', 'classname' => 'text-start'],
                    ['title' => 'Date/Time', 'width' => '20%'],
                ],
            ])
        </div>
    </div>
@endsection

@push('script')
    <script>
        var dtTable = null;

        $(document).ready(function() {
            dtTable = $('#notifications-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: @js(route('admin.notifications.index')),
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                order: [
                    [1, 'desc'] // created_at
                ],
                columns: [{
                        data: 'data',
                        name: 'data',
                        render: function(data, type, row, meta) {
                            let html =
                                `<div class="fw-bold"><a href="${row.notification_link ?? 'javascript:;'}"><i class="ti ti-bell"></i> ${data.title}</a></div>`;
                            if (data.message) {
                                html += `<div class="ms-4">${data.message}</div>`;
                            }
                            return html;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    }
                ],
            });
        });
    </script>
@endpush
