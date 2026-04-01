@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.escalations.index'),
    'filters' => true,
    'buttons' => [
        auth()->user()->can(Permission::ESCALATION_CREATE->value)
            ? ['label' => 'Add new escalation', 'icon' => 'ti ti-plus', 'url' => route('admin.escalations.create')]
            : null,
    ],
])

@include('admin.layouts.components.select2')
@include('admin.layouts.components.datatable')

@section('filters-body')
    <div class="row">
        @php($options = \App\Enums\EscalationStatus::selectArray())
        <x-admin.select name='filter_status' label='Status' class="filter-select2" :options="$options" placeholder="Select" />
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.layouts.components.datatable-header', [
                'id' => 'roles-table',
                'data' => [
                    ['title' => 'Sr.', 'classname' => 'text-center'],
                    ['title' => 'Track Id'],
                    ['title' => 'Customer'],
                    ['title' => 'Scam'],
                    ['title' => 'Type'],
                    ['title' => 'Assignee'],
                    ['title' => 'Escalated By'],
                    ['title' => 'Status'],
                    ['title' => 'Closed At'],
                    ['title' => 'Created At'],
                    ['title' => 'Action'],
                ],
            ])
        </div>
    </div>
    @include('admin.escalations._chat_window')
@endsection

@push('script')
    <script>
        var dtTable = null;

        const Action = {
            ...@js([
                'showUrl' => route('admin.escalations.show', ':id'),
                'editUrl' => route('admin.escalations.edit', ':id'),
                'deleteUrl' => route('admin.escalations.destroy', ':id'),
                'canDelete' => auth()->user()->can(Permission::ESCALATION_DELETE->value),
            ]),
            show: function(id) {
                const url = this.showUrl.replace(':id', id);
                return `<a href="javascript:;" data-chat-window-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-device-desktop-cog text-primary h1"></i></a>`;
            },
            delete: function(id) {
                if (!Action.canDelete)
                    return '';
                return `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>`;
            },
        };

        $(document).ready(function() {
            dtTable = $('#roles-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                order: [
                    [9, 'desc'] // created_at
                ],
                ajax: {
                    url: @js(route('admin.escalations.index')),
                    data: function(d) {
                        d.status = $('select[name="filter_status"]').val();
                    }
                },
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'track_id',
                        name: 'track_id',
                        render: function(data, type, row, meta) {
                            if (!data)
                                return noContentText();

                            const isRejected = row['is_rejected'];

                            let html = HtmlTag.span(data);
                            html += isRejected ? HtmlTag.icon('ti ti-ban',
                                'text-danger fs-3 ms-1') : ``;

                            return HtmlTag.div(html, 'd-flex align-items-center');
                        }
                    },
                    {
                        data: 'customer_details',
                        name: 'customer_details',
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'scam_details',
                        name: 'scam_details',
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
                        data: 'assignee',
                        name: 'assignee',
                        render: function(data, type, row, meta) {
                            const user = data ? parseJsonString(data) : null;
                            if (user) {
                                const img = avatarField(user.profileAvatar, 'me-2');
                                return `<div class="d-flex align-items-center">${img} ${user.name}</div>`;
                            }
                            return null;
                        }
                    },
                    {
                        data: 'escalated_by_user',
                        name: 'escalated_by_user',
                        render: function(data, type, row, meta) {
                            const user = data ? parseJsonString(data) : null;
                            if (user) {
                                const img = avatarField(user.profileAvatar, 'me-2');
                                return `<div class="d-flex align-items-center">${img} ${user.name}</div>`;
                            }
                            return null;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row, meta) {
                            return badge({
                                title: data,
                                color: row['status_color']
                            });
                        }
                    },
                    {
                        data: 'closed_at',
                        name: 'closed_at',
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
                            return Action.show(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                $('[data-delete-id]').on('click', function() {
                    const id = $(this).data('delete-id');
                    deleteResource({
                        id,
                        url: Action.deleteUrl.replace(':id', id),
                        dataTable: dtTable,
                        resourseName: 'escalation',
                        resourceArtical: 'an'
                    });
                });

                $('[data-chat-window-id]').on('click', handleChatWindowOnClick);
            });

            function handleChatWindowOnClick() {
                const id = $(this).data('chat-window-id');
                const modal = $('#chat_window_modal');
                modal.find('form').attr('action',
                    "{{ route('admin.escalation-chats.store', ':id') }}".replace(':id', id));
                openedEscalation = {
                    id,
                };
                refreshEscalationChat({
                    showLoader: true
                });
                $('#chat_window_modal').modal('show');
            }

            function deleteResource({
                id,
                url,
                dataTable,
                resourseName,
                resourceArtical = 'a'
            }) {
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to delete ${resourceArtical} <strong>${resourseName}</strong>.<br>If you proceed, you won't be able to revert this.`,
                    onConfirm: async function() {
                        await $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    response.toast && toast.open(response.toast);
                                    dataTable && dataTable.draw(false);
                                }
                            },
                            error: function() {
                                toast.open('error', 'Something Went Wrong!');
                            }
                        });
                    }
                });
            }

            FilterModule.registerDatatable(dtTable);
        });

        function apply_filter($oc) {
            $oc.offcanvas('hide');
            dtTable.draw(false);
        }
    </script>
@endpush
