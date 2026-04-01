@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => 'Whatsapp Message Logs',
    'breadcrumbs' => Breadcrumbs::render('admin.whatsapp-message-logs.index'),
])

@include('admin.layouts.components.datatable')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'whatsapp-logs-table',
                'data' => [
                    ['title' => 'Sr', 'classname' => 'text-start'],
                    ['title' => 'Whatsapp Number'],
                    ['title' => 'Recipient Detail'],
                    ['title' => 'Template Name'],
                    ['title' => 'Broadcast Name'],
                    ['title' => 'Response Status'],
                    ['title' => 'Date/Time'],
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
            ...@js([
                'showUrl' => route('admin.whatsapp-message-logs.show', ':id')
            ]),
            show: function(id) {
                const url = Action.showUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
            }
        };

        $(document).ready(function() {
            dtTable = $('#whatsapp-logs-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax:  @js(route('admin.whatsapp-message-logs.index')),
                order: [
                    [6, 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [
                    {
                        data: 'id',
                        render: function(data, type, row, meta) {
                            const sr = dtSerialNumber(meta);
                            return HtmlTag.span(`#${sr}`, 'text-secondary');
                        }
                    },
                    {
                        data: 'whatsapp_number',
                        name: 'whatsapp_number',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'recipient_detail',
                        name: 'recipient_detail',
                        render: function(data, type, row, meta) {

                            if(data) {
                                return data.type + ' - <br />' + data.data;
                            }

                            return noContentText();
                        }
                    },
                    {
                        data: 'template_name',
                        name: 'template_name',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'broadcast_name',
                        name: 'broadcast_name',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'response_status_code',
                        name: 'response_status_code',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return Action.show(data);
                        }
                    },
                ],
            });
        });
    </script>
@endpush
