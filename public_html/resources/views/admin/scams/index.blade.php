    @use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(App\Constants\Permission)
@use(App\View\ScamTable)

<style>
    .text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

</style>
@php
    $user = auth()->user();

    $pms = new \stdClass();
    $pms->sales_management = $user->can(Permission::SALES_MANAGEMENT);
    $pms->sales_management_self = $user->can(Permission::SALES_MANAGEMENT_SELF);
    $pms->drafting_management = $user->can(Permission::DRAFTING_MANAGEMENT);
    $pms->drafting_management_self = $user->can(Permission::DRAFTING_MANAGEMENT_SELF);
    $pms->service_management = $user->can(Permission::SERVICE_MANAGEMENT);
    $pms->service_management_self = $user->can(Permission::SERVICE_MANAGEMENT_SELF);
    $pms->scam_excel_import = $user->can(Permission::SCAM_EXCEL_IMPORT);
    $pms->sales_access = $pms->sales_management || $pms->sales_management_self;
    $pms->drafting_access = $pms->drafting_management || $pms->drafting_management_self;
    $pms->service_access = $pms->service_management || $pms->service_management_self;
    $pms->show_scam_source = $user->can(Permission::SHOW_SCAM_SOURCE);

    // sub-admin permissions (indexed on this view so we can disable the
    // dropdown exactly when the backend will reject the change)
    $pms->sub_admin_management = $user->can(Permission::SUB_ADMIN_MANAGEMENT);
    $pms->sub_admin_access = $pms->sub_admin_management;

    $pms->any_full_management = $pms->sales_management || $pms->drafting_management || $pms->service_management || $pms->sub_admin_management;

    $pms->bulkSelectedRequired = $pms->any_full_management;

    $pms->update_locked_sales_status = $user->can(Permission::UPDATE_LOCKED_SALES_STATUS);
    $pms->update_locked_drafting_status = $user->can(Permission::UPDATE_LOCKED_DRAFTING_STATUS);

    $pms->scam_recycle = $user->can(Permission::SCAM_RECYCLE);
    $pms->scam_bulk_update = $user->can(Permission::SCAM_BULK_UPDATE);
    $pms->scam_random_assigner = $user->can(Permission::SCAM_RANDOM_ASSIGNER);

    $scamTableView = new ScamTable;
@endphp

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scams.index'),
    'filters' => [
        'offcanvas-class' => 'offcanvas-full'
    ],
    'buttons' => [
        $pms->scam_excel_import
            ? [
                'label' => 'Random Assign',
                'icon' => 'ti ti-arrows-random',
                'variant' => 'outline-teal',
                'class' => '__random_assign_btn',
                 'invisible' => true,
            ]
            : null,
        $pms->scam_excel_import
            ? [
                'label' => 'Excel Import',
                'icon' => 'ti ti-file-arrow-left',
                'variant' => 'outline-primary',
                'class' => '__excel_import_btn',
            ]
            : null,
        $pms->scam_excel_import
            ? [
                'label' => 'FB Excel',
                'icon' => 'ti ti-file-download',
                'variant' => 'outline-secondary',
                'class' => '__fb_excel_import_btn',
            ]
            : null,
        $pms->scam_bulk_update
            ? [
                'label' => 'Bulk Update',
                'icon' => 'ti ti-pencil-check',
                'variant' => 'outline-dark',
                'class' => '__bulk_update_btn',
                'invisible' => true,
            ] : null,
        $pms->any_full_management
            ? [
                'label' => 'Bulk Assign',
                'icon' => 'ti ti-users-plus',
                'variant' => 'outline-info',
                'class' => '__bulk_assign_btn',
                'invisible' => true,
            ]
            : null,
        $pms->any_full_management
        ?[
            'label' => 'Excel Export',
            'icon' => 'ti ti-export',
            'variant' => 'outline-success',
            'class' => '__bulk_excel_export_btn',
            'invisible' => true,
        ]
        : null,
        $pms->scam_recycle
            ? [
                'label' => 'Recycle',
                'icon' => 'ti ti-refresh',
                'variant' => 'outline-danger',
                'class' => '__bulk_recycle_btn',
                'invisible' => true,
            ]
            : null,
        $user->can(Permission::SCAM_CREATE) ? ['label' => 'Add new scam', 'icon' => 'ti ti-plus', 'url' => route('admin.scams.create'), 'class' => '__quick_add_btn'] : null,
    ],
])

@include('admin.layouts.components.select2')
@include('admin.layouts.components.dropzone')
@include('admin.layouts.components.datatable')
@include('admin.layouts.components.datepicker')

@section('filters-body')
    @include('admin.scams._filter_body')
@endsection

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
    <style>
        /* Ensure fixed columns are above scrollable ones */
        .dtfc-fixed-left {
            z-index: 3 !important;
            background: white;
        }

        /* Keep select boxes and content behind fixed columns */
        .dataTables_wrapper .dataTables_scrollBody {
            position: relative;
            z-index: 1;
        }
    </style>
@endpush


@section('content')
    <div>
        <div class="row justify-content-between align-items-center">
            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                
                @php
                    $recordTypeOptions = [
                        '' => 'Display all records',
                        '1' => 'Hide all duplicate records',
                        '2' => 'Show only duplicate records'
                    ];

                    if($user->can(Permission::STATUS_UNASSIGNED_SCAM_LIST)) {
                        $recordTypeOptions['3'] = 'Show status unassigned records';
                    }
                    
                @endphp

                <x-admin.select name='records_type' id="records_type_select" class="form-select-sm select2 cursor-pointer" selected="1" :options="$recordTypeOptions" />
            </div>
            <div class="col-xl-10 col-lg-9 col-md-8 col-12">
                @include('admin.scams._status_color_hinting')
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                @include('admin.layouts.components.datatable-header', [
                    'id' => 'scams-table',
                    'cardClass' => $hasFrozenStatus ? 'border-danger' : '',
                    'data' => [
                        ['title' => 'Sr.'],
                        ['title' => '', 'permit' => $pms->bulkSelectedRequired],
                        ['title' => 'Track Id'],
                        ['title' => 'Customer'],
                        ['title' => 'Scam Type'],
                        ['title' => 'Scam Amount'],
                        [
                            'title' => 'Source',
                            'permit' => $pms->show_scam_source
                        ],
                        ['title' => 'Remark'],
                        [
                            'title' => 'Sales Assignee',
                            'permit' => $pms->sales_management || $pms->service_access || $pms->drafting_access,
                        ],
                        
                        [
                            'title' => 'Sales Status',
                            'permit' => $pms->sales_access || $pms->service_access || $pms->drafting_access,
                        ],
                        [
                            'title' => 'Sub Admin',
                            'permit' => $pms->sales_management || $pms->service_access || $pms->sub_admin_management,
                        ],
                        [
                            'title' => 'Drafting Assignee',
                            'permit' => $pms->sales_access || $pms->drafting_management || $pms->service_access,
                        ],
                        [
                            'title' => 'Drafting Status',
                            'permit' => $pms->sales_access || $pms->drafting_access || $pms->service_access,
                        ],
                        [
                            'title' => 'State',                         
                        ],
                        [
                            'title' => 'Service Assignee',
                            'permit' => $pms->service_management,
                        ],
                        
                        ['title' => $scamTableView->getDateHeaderName($user)],
                        ['title' => 'Action'],
                    ],
                ])
            </div>
        </div>

        @include('admin.scams._escalation_create_modal')
        @include('admin.scams._escalation_list_modal')
        @include('admin.scams._status_update_data_modal')
        @include('admin.scams._scam_import_offcanvas')
        @include('admin.scams._fb_excel_import_modal')
        @include('admin.escalations._chat_window')
        @include('admin.scams._scam_file_upload_modal')
        @include('admin.scams._scam_details_offcanvas')
        @include('admin.scams._random_scams_assign_modal')
        @include('admin.scams.detail-page.components._reject_status_modal')
        @include('admin.scams._bulk_excel_export_offcanvas')

        @if ($reminderScams->isNotEmpty())
            @include('admin.scams._reminder_scams_modal', ['$reminderScams' => $reminderScams])
        @endif

        @if ($pms->any_full_management)
            @include('admin.scams._bulk_assign_offcanvas', [
                'salesUsers' => $salesUsers,
                'scamStatuses' => $scamStatuses,
                'draftingUsers' => $draftingUsers,
                'serviceUsers' => $serviceUsers,
                'subAdminUsers' => $subAdminUsers,
            ])
        @endif
        
        @if ($pms->scam_bulk_update)
            @include('admin.scams._bulk_update_details')
        @endif

        <!-- Ajax Scam Modal -->
        <div class="modal fade" id="ajax-scam-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Scam</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center py-5"><i class="ti ti-loader ti-spin"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    
    {!! js_validation_custom_event(
        formRequestClass: \App\Http\Requests\Admin\ChangeScamStatusReviewRequest::class,
        formSelector: '#sales-status-reject-form',
        eventTargetSelector: '#sales-status-reject-modal',
        event: 'run-validation',
    ) !!}

    <script>
        var dtTable = null;

        const userType = "{{ $user->userType() }}";

        const [
            salesUsers,
            draftingUsers,
            serviceUsers,
            scamStatuses,
            firstDraftingStatus,
            SCAM_STATUS_SALES,
            SCAM_STATUS_DRAFTING,
            subAdminUsers,
            states
        ] = @js([$salesUsers, $draftingUsers, $serviceUsers, $scamStatuses, $firstDraftingStatus, \App\Enums\ScamStatusType::SALES, \App\Enums\ScamStatusType::DRAFTING, $subAdminUsers, $states]);

        const pms = @js($pms);

        var selectedScamId = null;

        const Action = {
            ...@js([
                'showUrl' => route('admin.scams.show', ':id'),
                'editUrl' => route('admin.scams.edit', ':id'),
                'deleteUrl' => route('admin.scams.destroy', ':id'),
                'assignUserUrl' => route('admin.scams.assign-user', ':id'),
                'changeScamStatusUrl' => route('admin.scams.change-status', ':id'),
                'bulkRecycleUrl' => route('admin.scams.bulk-recycle'),
                'canEdit' => $user->can(Permission::SCAM_UPDATE),
                'canDelete' => $user->can(Permission::SCAM_DELETE),
                'canEscalate' => $user->can(Permission::ESCALATION_CREATE),
            ]),

            show: function(id) {
                const url = this.showUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
            },
            escalate: function(id) {
                return Action.canEscalate ?
                    `<a href="javascript:;" data-escalate-scam-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-alert-triangle text-warning h1"></i></a>` :
                    ``;
            },
            edit: function(id) {
                if (!Action.canEdit)
                    return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                return Action.canDelete ?
                    `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>` :
                    '';
            },

            getSalesAssigneeSelect: function(userId, scamId) {
                let options = '';
                salesUsers.forEach(function(user) {
                    const disableOption = !user.status;
                    options +=
                        `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
                });
                const disable = !pms.sales_management;
                return `<select class="form-select table-td-select data-select sales-assignee-select select2" data-sales-assignee="${userId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Sales Assignee</option>${options}</select>`;
            },

            getSalesStatusSelect: function(statusId, scamId) {
                let options = '';

                const scam = dtGetScamById(scamId);
                const selectedStatus = scamStatuses.filter((item) => item.id == statusId)?.[0] ?? null;
                
                scamStatuses.forEach(function(status) {
                    if (status.type != SCAM_STATUS_SALES)
                        return;
                    options +=
                        `<option value="${status.id}" ${statusId && statusId == status.id ? 'selected' : ''}>${status.title}</option>`;
                });
                let disable = !pms.sales_access;

                if(!disable && scam.sales_status_record?.review === 'pending') {
                    disable = true;   
                }
                
                if(!disable && (selectedStatus && selectedStatus.is_lock && !pms.update_locked_sales_status)) {
                    disable = true;
                }

                return `<select class="form-select table-td-select sales-status-select select2" data-sales-status="${statusId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Sales Status</option>${options}</select>`;
            },

            getDraftingAssigneeSelect: function(userId, scamId) {
                let options = '';
                draftingUsers.forEach(function(user) {
                    const disableOption = !user.status;
                    options +=
                        `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
                });
                const disable = !pms.drafting_management;
                return `<select class="form-select table-td-select data-select drafting-assignee-select select2" data-drafting-assignee="${userId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Drafting Assignee</option>${options}</select>`;
            },

            
            getDraftingStatusSelect: function(statusId, scamId) {
                let options = '';
                
                const scam = dtGetScamById(scamId);
                const selectedStatus = scamStatuses.filter((item) => item.id == statusId)?.[0] ?? null;

                let optionStatuses = [];
                let showNullOption = true;

                optionStatuses = scamStatuses;

                optionStatuses.forEach(function(status) {
                    if (status.type != SCAM_STATUS_DRAFTING)
                        return;
                    options +=
                        `<option value="${status.id}" ${statusId && statusId == status.id ? 'selected' : ''}>${status.title}</option>`;
                });
                let disable = !pms.drafting_access;

                if(!disable && scam.drafting_status_record?.review === 'pending') {
                    disable = true;   
                }

                if(!disable && (selectedStatus && selectedStatus.is_lock && !pms.update_locked_drafting_status)) {
                    disable = true;
                }

                if(userType === 'sales') {
                    disable = true;
                }

                return `<select class="form-select table-td-select drafting-status-select select2" data-drafting-status="${statusId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}>${showNullOption ? `<option value>Select Drafting Status</option>` : ``}${options}</select>`;
            },

            getServiceAssigneeSelect: function(userId, scamId) {
                let options = '';
                serviceUsers.forEach(function(user) {
                    const disableOption = !user.status;
                    options +=
                        `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
                });
                return `<select class="form-select table-td-select data-select service-assignee-select select2" data-service-assignee="${userId}" data-scam-id="${scamId}" ${!pms.service_management ? 'disabled' : ''}><option value>Select Service Assignee</option>${options}</select>`;
            },
            getStateSelect: function(stateId, scamId) {
                let options = '';
                states.forEach(function(state) {
                    options +=
                        `<option value="${state.id}" ${stateId && stateId == state.id ? 'selected' : ''}>${state.name}</option>`;
                });
                return `<select class="form-select table-td-select data-select state-select select2" data-state="${stateId}" data-scam-id="${scamId}"><option value>Select State</option>${options}</select>`;
            },
            getSubAdminSelect: function(userId, scamId) {
                let options = '';
                subAdminUsers.forEach(function(user) {
                    const disableOption = !user.status;
                    options +=
                        `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
                });
                return `<select class="form-select table-td-select data-select sub-admin-select select2" data-sub-admin="${userId}" data-scam-id="${scamId}" ${!(pms.sales_management || pms.sub_admin_management) ? 'disabled' : ''}><option value>Select Sub Admin</option>${options}</select>`;
            },

        };

        function dtGetScamById(scamId) {
            const rows = dtAllRows(dtTable);
            return findObjectInArrayByKey(rows, 'id', scamId);
        }

        function disabledLineSelect2TemplateResult (data) {
            if (!data.id) return data.text;
            const originalOption = data.element;
            const $option = $('<span></span>').text(data.text);
            if ($(originalOption).prop('disabled')) {
                $option.addClass('text-decoration-line-through');
            }
            return $option;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        $(document).ready(function() {

            dtTable = $('#scams-table').DataTable({
                // responsive: true,
                scrollX: true, // Enables horizontal scrolling
                autoWidth: false, // Prevents automatic width shrinking
                searchDelay: 500,
                processing: true,
                serverSide: true,
                fixedColumns: {
                    leftColumns: 4
                },
                ajax: {
                    url: @js(route('admin.scams.index')),
                    data: function(d) {
                        d = withFilterData(d);
                        d.records_type = $('#records_type_select').val();
                    }
                },
                order: [
                    [@js($scamTableView->getOrderColumnId($user, false)), 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                select: {
                    style: 'multi+shift',
                    selector: 'td input.dt-select-checkbox'
                },
                columns: [
                    {
                        data: 'id',
                        render: function(data, type, row, meta) {
                            const sr = dtSerialNumber(meta);
                            return HtmlTag.span(`#${sr}`, 'text-secondary');
                        }
                    },
                    @if ($pms->bulkSelectedRequired)
                        {
                            data: 'id',
                            orderable: false,
                            render: DataTable.render.select(),
                            targets: 0
                        },
                    @endif {
                        data: 'track_id',
                        name: 'track_id',
                        render: function(data, type, row, meta) {
                            const id = row['id'];
                            const $elem = $(HtmlTag.span(data))
                                .attr('role', 'button')
                                .attr('onclick', `ScamDetailModule.open(${id})`)
                                .addClass('text-decoration-underline');
                            return $elem.outerHtml();
                        }
                    },
                    {
                        data: 'customer_info',
                        name: 'customer_info',
                        render: function(data, type, row, meta) {

                            const [fullname, phonenumber] = data;

                            const id = row['id'];

                            const text = fullname ?
                                `<span>${fullname}<br><span class="text-nowrap">(${phonenumber})</span>` :
                                `<span>${phonenumber}</span></span>`;

                            const $el = $(text).attr('role', 'button')
                                .attr('onclick', `ScamDetailModule.open(${id})`)
                                .addClass('text-decoration-underline');

                            return $el.outerHtml();
                        }
                    },
                    {
                        data: 'scam_type',
                        name: 'scam_type',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    {
                        data: 'formatted_scam_amount',
                        name: 'scam_amount',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    @if($pms->show_scam_source)
                    {
                        data: 'scam_source',
                        name: 'scam_source_id',
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            if(!data) {
                                return noContentText();
                            }

                            let icon = null;
                            let label = '';

                            if(data.slug == 'whatsapp') {
                                icon = 'brand-whatsapp text-success fs-2';
                            } else if(data.slug == 'instagram') {
                                icon = 'brand-instagram text-danger fs-2';
                            } else if(data.slug.startsWith('website')) {
                                icon = 'world text-primary fs-2';
                                label = data.title;
                            }

                            if(icon) {
                                return `<i class="ti ti-${icon}" title="${label}"></i>`;
                            }

                            return data.title;
                        }
                    },
                    @endif
                    {
                        data: 'remark',
                        name: 'remark',
                        render: function (data, type, row, meta) {

                            const hasRemark = !!data;
                            const remarkEncoded = hasRemark ? encodeURIComponent(data) : '';
                            const icon = hasRemark ? 'ti ti-edit' : 'ti ti-plus';
                            const btnVariant = hasRemark ? 'btn-outline-secondary' : 'btn-outline-success';

                            const btn = `
                                <button 
                                    type="button"
                                    class="btn btn-sm ${btnVariant} ms-2 __edit_remark_btn"
                                    data-scam-id="${row.id}"
                                    data-remark="${remarkEncoded}"
                                    title="${hasRemark ? 'View / Edit remark' : 'Add remark'}"
                                >
                                    <i class="${icon}"></i>
                                </button>
                            `;

                            return btn;
                        }
                    },
                    @if ($pms->sales_access || $pms->service_access || $pms->drafting_access)
                        @if ($pms->sales_management || $pms->service_access || $pms->drafting_access)
                            {
                                data: 'sales_assignee_id',
                                name: 'sales_assignee_id',
                                searchable: false,
                                orderable: false,
                                render: function(data, type, row, meta) {
                                    
                                    let html = '';

                                    const previousAssigneeName = row.latest_sales_status_unassign_record?.assignee?.name;
                                    if(previousAssigneeName) {
                                        html += `<span class="text-info" title="previous assignee">${previousAssigneeName}</span><br/>`;
                                    }
                                    
                                    html += Action.getSalesAssigneeSelect(data, row.id);
                                    return html;
                                }
                            },
                        @endif {
                            data: 'sales_status_id',
                            name: 'sales_status_id',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row, meta) {
                                let html = Action.getSalesStatusSelect(data, row.id);
                                if(row.registered_amount > 0) {
                                    html += `<div class="mt-1 text-nowrap"><span class="badge bg-label-info">Reg. Amt: ${row.formatted_registered_amount}</span></div>`;
                                    if(userType !== 'drafting') {
                                        html += `<button 
                                            class="btn btn-sm btn-outline-primary register-again-btn"
                                            data-scam-id="${row.id}"
                                            data-status-id="${data}"
                                            title="Register Again"
                                        >
                                            <i class="ti ti-plus"></i>
                                        </button>`;
                                    }
                                }
                                return html;
                            },
                            createdCell: function(td, cellData, rowData, rowIndex, colIndex) {
                                const reviewStatus = rowData?.sales_status_record?.review;
                                if(reviewStatus) {
                                    $(td).css('background-color', rowData.sales_status_review_color);
                                }
                            }
                        },
                    @endif
                    @if ($pms->sales_management || $pms->service_access || $pms->sub_admin_management)
                    {
                        data: 'sub_admin_id',
                        name: 'sub_admin_id',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return Action.getSubAdminSelect(data, row.id);
                        }
                    },
                    @endif
                    @if ($pms->sales_access || $pms->drafting_access || $pms->service_access)
                        @if ($pms->sales_access || $pms->drafting_management || $pms->service_access)
                            {
                                data: 'drafting_assignee_id',
                                name: 'drafting_assignee_id',
                                searchable: false,
                                orderable: false,
                                render: function(data, type, row, meta) {
                                    let html = '';

                                    const previousAssigneeName = row.latest_drafting_status_unassign_record?.assignee?.name;
                                    if(previousAssigneeName) {
                                        html += `<span class="text-info" title="previous assignee">${previousAssigneeName}</span><br/>`;
                                    }
                                    
                                    html += Action.getDraftingAssigneeSelect(data, row.id);
                                    return html;
                                }
                            },
                        @endif {
                            data: 'drafting_status_id',
                            name: 'drafting_status_id',
                            searchable: false,
                            orderable: false,
                            render: function(data, type, row, meta) {
                                let html = Action.getDraftingStatusSelect(data, row.id);
                                
                                return html;
                            },
                            createdCell: function(td, cellData, rowData, rowIndex, colIndex) {
                                const reviewStatus = rowData?.drafting_status_record?.review;
                                if(reviewStatus) {
                                    $(td).css('background-color', rowData.drafting_status_review_color);
                                }
                            }
                        },
                    @endif
                    {
                        data: 'state',
                        name: 'state',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return Action.getStateSelect(data, row.id);
                        }
                    },
                    @if ($pms->service_access)
                        @if ($pms->service_management)
                            {
                                data: 'service_assignee_id',
                                name: 'service_assignee_id',
                                searchable: false,
                                orderable: false,
                                render: function(data, type, row, meta) {
                                    return Action.getServiceAssigneeSelect(data, row.id);
                                }
                            },
                        @endif
                    @endif {
                        data: @js($scamTableView->getDateFieldName($user)),
                        name: @js($scamTableView->getDateFieldName($user)),
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return data;
                        }
                    },
                    
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return Action.escalate(data) + Action.edit(data) + Action.delete(data);
                        }
                    },
                   

                ],
            }).on('draw responsive-display', function() {
                initSelect2($('.table-td-select'), {
                    templateResult: disabledLineSelect2TemplateResult
                });
                setupDtSelects();
            }).on('init', function() {
                setupDtSelects();
            }).on('select', function(e, dt, type, indexes) {
                $(dt.rows(indexes).nodes()).addClass('dt-custom-select');
            }).on('deselect', function(e, dt, type, indexes) {
                $(dt.rows(indexes).nodes()).removeClass('dt-custom-select');
            });

            $("#scams-table").on('click', '[data-delete-id]', deleteScam);
            $("#scams-table").on('click', '[data-escalate-scam-id]', escalateScam);

            // Remark add/edit button
            $('#scams-table').on('click', 'button.__edit_remark_btn', function(e) {
                e.preventDefault();
                const scamId = $(this).data('scam-id');
                const remarkEncoded = $(this).attr('data-remark') || '';
                const remark = remarkEncoded ? decodeURIComponent(remarkEncoded) : '';
                const action = "{{ route('admin.scams.update-remark', ':id') }}".replace(':id', scamId);

                const formHtml = `<form action="${action}" method="POST">
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="mb-3">
                        <label class="form-label">Remark</label>
                        <textarea name="remark" class="form-control" rows="4">${escapeHtml(remark)}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>`;

                openScamModalWithHtml(formHtml);
            });

            $(document).on('app:main_table_redraw app:status_update_with_data_success app:status_change_with_data_modal_closed app:status_data_update_modal_closed', function() {
                dtTable.draw(false);
            });

            function assigneeSelectHandler({
                $selectElement,
                type,
                originalAssigneeId
            }) {
                const selectedAssigneeId = $selectElement.val();
                const scam_id = $selectElement.data('scam-id');
                let message = '';
                let success_message = '';
                if (originalAssigneeId == null) {
                    message = `You are about to assign this scam to a ${type} user.`;
                    success_message = `${ucFirst(type)} user has been assigned.`;
                } else if (selectedAssigneeId) {
                    message = `You are about to change the assigned ${type} user for this scam.`;
                    success_message = `${ucFirst(type)} assigned user has been changed.`;
                } else {
                    message = `You are about to remove the assigned ${type} user from this scam.`;
                    success_message = `${ucFirst(type)} assigned user has been removed.`;
                }
                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: message,
                    onConfirm: async function() {
                        const url = Action.assignUserUrl.replace(':id', scam_id);
                        const assignee_id = $selectElement.val();
                        await $.post(url, {
                            assignee_id,
                            type
                        }, function(res) {
                            if (res.success) {
                                toast.open({
                                    type: 'success',
                                    message: success_message
                                });
                            } else {
                                toast.open({
                                    type: 'error',
                                    message: res.toast?.message || 'Assignment failed.'
                                });
                            }
                            dtTable.draw(false);
                        });
                    },
                    onCancel: function() {
                        $selectElement.val(originalAssigneeId ?? '')
                            .trigger('change');
                    }
                });
            }

            function statusSelectHandler({
                $selectElement,
                type,
                originalStatusId
            }) {

                if ($selectElement.data('cancel')) {
                    $selectElement.removeData('cancel');
                    return;
                }
                const selectedStatusId = $selectElement.val();
                const scam_id = $selectElement.data('scam-id');
                const scam = dtGetScamById(scam_id);

                const statusObject = findObjectInArrayByKey(scamStatuses, 'id', selectedStatusId);

                let message = '';
                let success_message = '';
                if (originalStatusId == null) {
                    message = `You are about to set ${type} status of this scam.`;
                    success_message = `${ucFirst(type)} status has been set`;
                } else if (selectedStatusId) {
                    message = `You are about to change the ${type} status of this scam.`;
                    success_message = `${ucFirst(type)} status has been changed`;
                } else {
                    message = `You are about to remove the ${type} status of this scam.`;
                    success_message = `${ucFirst(type)} status has been removed`;
                }

                if(statusObject?.status_update_fields_exists) {
                    $(document).trigger('app:status-update-data-modal.open', {
                        scamId: scam.id,
                        statusId: statusObject.id
                    });
                    return;
                }

                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: message,
                    onConfirm: async function() {
                        const url = Action.changeScamStatusUrl.replace(':id', scam.id);
                        const status_id = $selectElement.val();
                        await $.post(url, {
                            status_id,
                            type
                        }, function(res) {
                            if (res.success) {
                                toast.open({
                                    type: 'success',
                                    message: success_message
                                });
                            } else {
                                res.toast && toast.open(res.toast);
                            }
                            dtTable.draw(false);
                        });
                    },
                    onCancel: function() {
                        $selectElement.val(originalStatusId ?? '')
                            .data('cancel', true)
                            .trigger('change');
                    }
                });
            }


            @if ($pms->sales_access)
                // Sales Assignee Select
                @if ($pms->sales_management)
                    $('#scams-table').on('change', '.sales-assignee-select', function() {
                        assigneeSelectHandler({
                            $selectElement: $(this),
                            type: 'sales',
                            originalAssigneeId: $(this).data('sales-assignee')
                        });
                    });
                @endif

                // Sales Status Select
                $('#scams-table').on('change', '.sales-status-select', function() {
                    statusSelectHandler({
                        $selectElement: $(this),
                        type: 'sales',
                        originalStatusId: $(this).data('sales-status'),
                    });
                });
            @endif

            // Register Again Button
            $('#scams-table').on('click', '.register-again-btn', function() {
                const scamId = $(this).data('scam-id');
                const statusId = $(this).data('status-id');
                $(document).trigger('app:status-update-data-modal.open', {
                    scamId: scamId,
                    statusId: statusId
                });
            });

            @if ($pms->drafting_access)
                // Drafting Assignee Select
                @if ($pms->drafting_management)
                    $('#scams-table').on('change', '.drafting-assignee-select', function() {
                        assigneeSelectHandler({
                            $selectElement: $(this),
                            type: 'drafting',
                            originalAssigneeId: $(this).data('drafting-assignee')
                        });
                    });
                @endif

                // Drafting Status Select
                $('#scams-table').on('change', '.drafting-status-select', function() {
                    statusSelectHandler({
                        $selectElement: $(this),
                        type: 'drafting',
                        originalStatusId: $(this).data('drafting-status')
                    });
                });
            @endif

            @if ($pms->drafting_access || $pms->service_access)
                // Sub-Admin Assignee Select
                $('#scams-table').on('change', '.sub-admin-select', function() {
                    assigneeSelectHandler({
                        $selectElement: $(this),
                        type: 'sub_admin',
                        originalAssigneeId: $(this).data('sub-admin')
                    });
                });
            @endif

            @if ($pms->sales_access)
                // Service Assignee Select
                $('#scams-table').on('change', '.service-assignee-select', function() {
                    assigneeSelectHandler({
                        $selectElement: $(this),
                        type: 'service',
                        originalAssigneeId: $(this).data('service-assignee')
                    });
                });
            @endif

            // State Select
            $('#scams-table').on('change', '.state-select', function() {
                const $selectElement = $(this);
                const scam_id = $selectElement.data('scam-id');
                const state_id = $selectElement.val();
                const url = "{{ route('admin.scams.change-state', ':id') }}".replace(':id', scam_id);
                
                $.post(url, { state_id }, function(res) {
                    if (res.success) {
                        toast.open({ type: 'success', message: 'State updated successfully.' });
                    } else {
                        toast.open({ type: 'error', message: res.toast?.message || 'Update failed.' });
                    }
                    dtTable.draw(false);
                });
            });



            ajaxForm('#create-escalation-form', {
                handleToast: true,
                success: function(res) {
                    close_all_modals();
                    const escalationId = res.data.id;
                    openChatWindow(escalationId);
                }
            });

            $('#records_type_select').on('change', function () {
                dtTable && dtTable.draw();
            });

            FilterModule.registerDatatable(dtTable);

            // Quick Add / Edit Modal handling
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

          function openScamModalWithHtml(html) {
            const $modal = $('#ajax-scam-modal');

            $modal.find('.modal-body').html(html);

            // destroy previous validation
            $modal.find('form').each(function(){
                if($(this).data('validator')) {
                    $(this).validate().destroy();
                }
            });

            // execute scripts from blade
            $modal.find('script').each(function () {
                $.globalEval(this.innerHTML);
            });

            initSelect2($modal.find('.select2'), { dropdownParent: $modal });
            initSelect2Ajax($modal.find('.select2-ajax'), { dropdownParent: $modal });
            initDatePicker($modal.find('.datepicker'));

            $modal.modal('show');
        }


            // Open create form in modal
            $('body').on('click', '.__quick_add_btn, a[href="{{ route('admin.scams.create') }}"]', function(e) {
                e.preventDefault();
                const url = $(this).attr('href') || $(this).data('url') || "{{ route('admin.scams.create') }}";
                overlayLoader.show();
                $.get(url, function(res) {
                    // If response is HTML page, extract form
                    const $form = $(res).find('form').first();
                    if ($form.length) {
                        openScamModalWithHtml($form);
                    } else {
                        // Fallback: if controller returns JSON with html
                        openScamModalWithHtml(res.html || res);
                    }
                    overlayLoader.hide();
                }).fail(function() { overlayLoader.hide(); toast.open({ type: 'error', message: 'Unable to load form.' }); });
            });

            // Open edit form in modal
            $('#scams-table').on('click', 'a:has(i.ti-edit)', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                overlayLoader.show();
                $.get(url, function(res) {
                    const $form = $(res).find('form').first();
                    if ($form.length) {
                        openScamModalWithHtml($form);
                    } else {
                        openScamModalWithHtml(res.html || res);
                    }
                    overlayLoader.hide();
                }).fail(function() { overlayLoader.hide(); toast.open({ type: 'error', message: 'Unable to load edit form.' }); });
            });

            // Handle form submit via AJAX
            $('#ajax-scam-modal').on('submit', 'form', function(e) {
                e.preventDefault();
                const $form = $(this);
                const action = $form.attr('action');
                const method = ($form.find('input[name="_method"]').val() || $form.attr('method') || 'POST').toUpperCase();
                const data = new FormData(this);
                overlayLoader.show();
                $.ajax({
                    url: action,
                    // Use POST for method overrides (PATCH/PUT/DELETE) so Laravel handles _method reliably
                    type: ['PUT', 'PATCH', 'DELETE'].includes(method) ? 'POST' : method,
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        overlayLoader.hide();
                        if (resp.success) {
                            $('#ajax-scam-modal').modal('hide');
                            dtTable.draw(false);
                            if (resp.toast) {
                                toast.open(resp.toast);
                            } else {
                                toast.open({ type: 'success', message: 'Saved!' });
                            }
                        } else {
                            toast.open({ type: 'error', message: resp.toast?.message || 'Save failed.' });
                        }
                    },
                    error: function(xhr) {
                        overlayLoader.hide();
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            $form.find('.is-invalid').removeClass('is-invalid');
                            $form.find('.invalid-feedback').remove();
                            for (const key in errors) {
                                const $input = $form.find(`[name="${key}"]`);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    $input.after(`<div class="invalid-feedback">${errors[key][0]}</div>`);
                                }
                            }
                                } else if (xhr.status === 403) {
                            const message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Unauthorized Access!';
                            toast.open({ type: 'error', message: message });
                        } else {
                            toast.open({ type: 'error', message: 'An error occurred.' });
                        }
                    }
                });
            });

        });

        function setupDtSelects(selector) {
            $('input.dt-select-checkbox').addClass('form-check-input cursor-pointer');
            $('input.dt-select-checkbox').on('change', function() {
                const selectedRows = dtSelectedRows(dtTable);
                pageButtonVisibility('.__bulk_assign_btn, .__bulk_recycle_btn, .__bulk_update_btn, .__bulk_excel_export_btn', selectedRows.length > 0);
            });
        }

        function deleteScam() {
            const id = $(this).data('delete-id');
            Popup.askConfirmation({
                variant: 'danger',
                icon: 'ti ti-trash',
                message: `You are about to delete the <strong>user</strong>.<br>If you proceed, you won't be able to revert this.`,
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

        function escalateScam() {

            const scamId = $(this).data('escalate-scam-id');
            selectedScamId = scamId;

            const escalationsUrl = "{{ route('admin.scams.all-scam-escalations', ':id') }}".replace(':id',
                scamId);

            const escalationListItemTemplate = $('#escalation-list-item-container').html();

            const $escalationListContainer = $('#escalation-list-container');

            const escalationLink = '{{ route('admin.escalations.show', ':id') }}';

            $.ajax({
                url: escalationsUrl,
                method: "GET",
                beforeSend: () => overlayLoader.show(),
                success: function(res) {
                    const escalations = res.data;
                    selectedScamId = scamId;

                    if (escalations.length > 0) {

                        let html = '';

                        escalations.forEach(function(escalation) {

                            const url = escalationLink.replace(':id', escalation.id);

                            const listItem = escalationListItemTemplate
                                .replace('{escalationId}', escalation.id)
                                .replace('{track_id}', escalation.track_id)
                                .replace('{type}', escalation.type_label)
                                .replace('{status}', escalation.status_label)
                                .replace('{status_color}', escalation.status_color)
                                .replace('{created_at}', escalation.created_at_formatted)
                                .replace('{link_1}', url)
                                .replace('{link_2}', url);

                            html += listItem;
                        });

                        $escalationListContainer.html(html);

                        $('#escalation-list-modal').modal('show');
                        return;
                    } else {
                        showCreateEscalationModal(scamId);
                    }
                },
                complete: () => overlayLoader.hide(),
            });

        }

        function openChatWindow(escalationId) {
            close_all_modals();
            const id = escalationId;
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

        function handleScamReview(scamId, type, status) {

            const $modal = $('#sales-status-reject-modal');

            const url = '{{ route('admin.scams.change-scam-status-review', ':id') }}'.replace(':id', scamId);

            function handleSuccess(res) {
                if(res.success) {
                $modal.modal('hide');
                if (typeof dtTable !== 'undefined' && dtTable !== null) {
                    dtTable.draw(false);
                }
                if (typeof ScamDetailModule !== "undefined" && ScamDetailModule.refresh) {
                    ScamDetailModule.refresh();
                }
            }
            }

            if(status === 'approved') {

                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: `You are about to approve this case ${type} status.`,
                    onConfirm: async function() {
                        await $.post(url, {
                            type,
                            review: status,
                        }, handleSuccess);
                    }
                });

            } else if(status === 'reject') {
                
                $modal.find('.modal-body').html($('#reject-status-card-template').html());

                const $form = $modal.find('form#sales-status-reject-form');

                $form.attr('action', url);
                $form.find('input[name="type"]').val(type);
                
                $form.validate().destroy();
                $modal.trigger('run-validation');

                ajaxForm('#sales-status-reject-form', {
                    handleToast: true,
                    success: handleSuccess
                });
                
                $modal.modal('show');
                
            }
        }

        @if ($pms->scam_recycle)          
            function bulkRecycleScams() {
                const selectedScams = dtSelectedRows(dtTable, 'id');

                if (selectedScams.length <= 0) {
                    toast.open({
                        type: 'warning',
                        message: 'No records are selected!'
                    });
                    return;
                }

                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: `You are about to recycle selected cases.`,
                    onConfirm: async function() {
                        console.log(selectedScams);
                        await runAjax({
                            url: Action.bulkRecycleUrl,
                            data: {
                                scams: selectedScams
                            },
                            method: 'POST',
                            handleToast: true,
                            success: function(response) {
                                dtTable.draw(false);
                            }
                        });
                    }
                });
            }
            $(document).ready(function() {
                $('.__bulk_recycle_btn').on('click', bulkRecycleScams);
            });
        @endif
        
        // Open Bulk Excel Export Offcanvas
        $('body').on('click', '.__bulk_excel_export_btn', function () {

            const selectedIds = dtSelectedRows(dtTable, 'id');

            if (selectedIds.length === 0) {
                toast.open({
                    type: 'warning',
                    message: 'Please select at least one record to export.'
                });
                return;
            }

            $('#export_scam_ids').val(selectedIds.join(','));

            const offcanvasEl = document.getElementById('bulkExcelExportOffcanvas');
            const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
            offcanvas.show();
        });

    </script>
@endpush
