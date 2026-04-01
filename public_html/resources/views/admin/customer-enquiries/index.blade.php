@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@php
    $user = auth()->user();
    $userType = $user->userType();
    $isUserDrafting = $user->getRoleString() === 'drafting';

    if ($userType === 'drafting') {
        $pageTitle = 'Drafting Escalations';
    } elseif ($userType === 'sales') {
        $pageTitle = 'Sales Enquiries';
    } else {
        $pageTitle = $assigneeType == 'sales' ?  'Sales Enquiries' : 'Drafting Escalations';
    }

@endphp

@php

    $pms = new \stdClass();
    $pms->sales_management = $user->can(Permission::SALES_MANAGEMENT);
    $pms->sales_management_self = $user->can(Permission::SALES_MANAGEMENT_SELF);
    $pms->drafting_management = $user->can(Permission::DRAFTING_MANAGEMENT);
    $pms->drafting_management_self = $user->can(Permission::DRAFTING_MANAGEMENT_SELF);
    $pms->sales_access = $pms->sales_management || $pms->sales_management_self;
    $pms->drafting_access = $pms->drafting_management || $pms->drafting_management_self;
    $pms->update_locked_sales_status = $user->can(Permission::UPDATE_LOCKED_SALES_STATUS);
    $pms->update_locked_drafting_status = $user->can(Permission::UPDATE_LOCKED_DRAFTING_STATUS);

@endphp

@extends('admin.layouts.app', [
    'pageTitle' => $pageTitle,
    'breadcrumbs' => $isUserDrafting ?  Breadcrumbs::render('admin.escalations.index') :  Breadcrumbs::render('admin.customer-enquiries.index'),
    'filters' => true,
    'buttons' => [
        [
            'label' => 'Bulk Assign',
            'icon' => 'ti ti-users-plus',
            'variant' => 'outline-info',
            'class' => '__bulk_assign_btn',
            'invisible' => true,
        ]
    ]
])

@include('admin.layouts.components.select2')
@include('admin.layouts.components.datatable')
@include('admin.layouts.components.datepicker')
@include('admin.layouts.components.dropzone')


@section('filters-body')
    @include('admin.customer-enquiries.filter')
@endsection

@section('content')
    <div>
        @if ($userType === 'admin')
            <div class="row justify-content-start align-items-center">
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <x-admin.select name='records_type' id="records_type_select" class="form-select-sm select2 cursor-pointer" selected="2" :options="[
                        '' => 'Display all records',
                        '1' => 'Assigned records (manually)',
                        '2' => 'Not assigned records (manually)',
                    ]" />
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <x-admin.select name='bypassed' id="bypassed_select" class="form-select-sm select2 cursor-pointer" selected="0" :options="[
                        '0' => 'Hide Bypassed records',
                        '1' => 'Show Only bypassed records',
                    ]" />
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                @include('admin.layouts.components.datatable-header', [
                    'cardClass' => $hasFrozenEnquiries ? 'border-danger' : '',
                    'id' => 'customer-enquiries-table',
                    'data' => [
                        ['title' => 'Sr.'],
                        ['title' => ''],
                        ['title' => 'Customer'],
                        ['title' => 'Occurrence'],
                        ['title' => 'Source'],
                        ['title' => 'Scam Amount'],
                        ['title' => 'Sales Assignee', 'permit' => $userType === 'sales' || $userType === 'admin', 'visible' => $assigneeType === 'sales'],
                        ['title' => 'Sales Enquiry Status', 'permit' => $userType === 'sales' || $userType === 'admin', 'visible' => $assigneeType === 'sales'],
                        ['title' => 'Drafting Assignee', 'permit' => $userType === 'drafting' || $userType === 'admin', 'visible' => $assigneeType === 'drafting'],
                        ['title' => 'Drafting Enquiry Status', 'permit' => $userType === 'drafting' || $userType === 'admin', 'visible' => $assigneeType === 'drafting'],
                        ['title' => 'Case Sales Status', 'permit' => $userType === 'sales' || $userType === 'admin', 'visible' => $assigneeType === 'sales'],
                        ['title' => 'Case Drafting Status', 'permit' => $userType === 'drafting' || $userType === 'admin', 'visible' => $assigneeType === 'drafting'],
                        ['title' => 'Created At'],
                        ['title' => 'Action']
                    ],
                ])
            </div>
        </div>
    </div>
    @include('admin.customer-enquiries._status_update_data_modal')
    @include('admin.customer-enquiries._scam_status_update_modal')
    @include('admin.customer-enquiries._bulk_assign_offcanvas', [
        'salesUsers' => $salesUsers,
        'draftingUsers' => $draftingUsers,
    ])
    @include('admin.scams._status_update_data_modal')
@endsection

@push('script')
  <script>
    var dtTable = null;

    const pms = @js($pms);

    const userType = "{{ $user->userType() }}";

    const [
        salesUsers,
        draftingUsers,
        customerEnquiryStatuses,
        scamStatuses,
        firstDraftingStatus,
        SCAM_STATUS_SALES,
        SCAM_STATUS_DRAFTING
    ] = @js([$salesUsers, $draftingUsers, $customerEnquiryStatuses, $scamStatuses, $firstDraftingStatus, \App\Enums\CustomerEnquiryStatusType::SALES, \App\Enums\CustomerEnquiryStatusType::DRAFTING]);

    const Action = {
        ...@js([
            'showUrl' => route('admin.customer-enquiries.show', ':id'),
            'deleteUrl' => route('admin.customer-enquiries.destroy', ':id'),
            'assignUserUrl' => route('admin.scams.assign-user', ':id'),
            'changeStatusUrl' => route('admin.customer-enquiries.change-status', ':id'),
            'changeScamStatusUrl' => route('admin.scams.change-status', ':id'),
            'canDelete' => $user->can(Permission::CUSTOMER_ENQUIRY_DELETE)
        ]),

        show: function(id) {
            const url = this.showUrl.replace(':id', id);
            return `<a href="${url}" class="cursor-pointer mx-1"><i class="ti ti-eye text-warning h1"></i></a>`;
        },

        delete: function(id) {
            return Action.canDelete ?
                `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>` :
                '';
        },

        getSalesAssigneeSelect: function(userId, scamId, enquiryId) {
            let options = '';
            salesUsers.forEach(function(user) {
                const disableOption = !user.status;
                options +=
                    `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
            });
            const disable = {{ $userType === 'admin' ? 'false' : 'true' }};
            return `<select class="form-select table-td-select sales-assignee-select select2" data-sales-assignee="${userId}" data-enquiry-id="${enquiryId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Sales Assignee</option>${options}</select>`;
        },

        getDraftingAssigneeSelect: function(userId, scamId, enquiryId) {
            let options = '';
            draftingUsers.forEach(function(user) {
                const disableOption = !user.status;
                options +=
                    `<option value="${user.id}" ${userId && userId == user.id ? 'selected' : ''} ${disableOption ? 'disabled' : ''}>${user.name}</option>`;
            });
            const disable = {{ $userType === 'admin' ? 'false' : 'true' }};
            return `<select class="form-select table-td-select drafting-assignee-select select2" data-drafting-assignee="${userId}" data-enquiry-id="${enquiryId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Drafting Assignee</option>${options}</select>`;
        },

        getSalesStatusSelect: function(statusId, enquiryId) {
            let options = '';

            const selectedStatus = customerEnquiryStatuses.filter((item) => item.id == statusId)?.[0] ?? null;
            
            customerEnquiryStatuses.forEach(function(status) {
                if (status.type != SCAM_STATUS_SALES)
                    return;
                options +=
                    `<option value="${status.id}" ${statusId && statusId == status.id ? 'selected' : ''}>${status.title}</option>`;
            });
            let disable = false;

            return `<select class="form-select table-td-select sales-status-select select2" data-status="${statusId}" data-enquiry-id="${enquiryId}" ${disable ? 'disabled' : ''}><option value>Select Sales Status</option>${options}</select>`;
        },

        getDraftingStatusSelect: function(statusId, enquiryId) {
            let options = '';
            
            const selectedStatus = customerEnquiryStatuses.filter((item) => item.id == statusId)?.[0] ?? null;

            customerEnquiryStatuses.forEach(function(status) {
                if (status.type != SCAM_STATUS_DRAFTING)
                    return;
                options +=
                    `<option value="${status.id}" ${statusId && statusId == status.id ? 'selected' : ''}>${status.title}</option>`;
            });
            let disable = false;

            return `<select class="form-select table-td-select drafting-status-select select2" data-status="${statusId}" data-enquiry-id="${enquiryId}" ${disable ? 'disabled' : ''}><option value>Select Drafting Status</option>${options}</select>`;
        },

        getScamSalesStatusSelect: function(statusId, scamId, enquiryId) {
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

            return `<select class="form-select table-td-select scam-sales-status-select select2" data-sales-status="${statusId}" data-enquiry-id="${enquiryId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Sales Status</option>${options}</select>`;
        },

        getScamDraftingStatusSelect: function(statusId, scamId, enquiryId) {
            let options = '';
            
            const scam = dtGetScamById(scamId);
            const selectedStatus = scamStatuses.filter((item) => item.id == statusId)?.[0] ?? null;

            scamStatuses.forEach(function(status) {
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

            return `<select class="form-select table-td-select scam-drafting-status-select select2" data-drafting-status="${statusId}" data-enquiry-id="${enquiryId}" data-scam-id="${scamId}" ${disable ? 'disabled' : ''}><option value>Select Drafting Status</option>${options}</select>`;
        },

    };

    function dtGetScamById(scamId) {
        const rows = dtAllRows(dtTable);
        return findObjectInArrayByKey(rows.map((r) => r.scam), 'id', scamId);
    }

    function dtGetEnquiryById(enquiryId) {
        const rows = dtAllRows(dtTable);
        return findObjectInArrayByKey(rows, 'id', enquiryId);
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

    $(document).ready(function() {

        dtTable = $('#customer-enquiries-table').DataTable({
            responsive: true,
            searchDelay: 500,
            processing: true,
            serverSide: true,
            ajax: {
                url: @js(route('admin.customer-enquiries.index', ['type' => $assigneeType])),
                data: function(d) {
                    d = withFilterData(d);
                    d.records_type = $('#records_type_select').val();
                    d.bypassed = $('#bypassed_select').val();
                }
            },
            order: [
                [9, 'desc'] // created_at
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
                {
                    data: 'id',
                    orderable: false,
                    render: DataTable.render.select(),
                    targets: 0
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

                        const redirUrl = Action.showUrl.replace(':id', id);

                        const $el = $(text).attr('role', 'button')
                            .attr('onclick', `redirect("${redirUrl}")`)
                            .addClass('text-decoration-underline');

                        return $el.outerHtml();
                    }
                },
                {
                    data: 'occurrence',
                    name: 'occurrence',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return data;
                    }
                },
                {
                    data: 'source',
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
                {
                    data: 'scam_amount',
                    name: 'scam_amount',
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return data ?? noContentText();
                    }
                },
                @if ($assigneeType === 'sales' && ($userType === 'sales' || $userType === 'admin'))
                {
                    data: 'sales_assignee_id',
                    name: 'sales_assignee_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        if(!row.scam) {
                            return noContentText();
                        }
                        return Action.getSalesAssigneeSelect(data, row.scam?.id, row.id);
                    }
                },
                @endif
                @if ($assigneeType === 'sales' && ($userType === 'sales' || $userType === 'admin'))
                {
                    data: 'sales_status_id',
                    name: 'sales_status_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return Action.getSalesStatusSelect(data, row.id);
                    }
                },
                @endif
                @if ($assigneeType === 'drafting' && ($userType === 'drafting' || $userType === 'admin'))
                {
                    data: 'drafting_assignee_id',
                    name: 'drafting_assignee_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        if(!row.scam) {
                            return noContentText();
                        }
                        return Action.getDraftingAssigneeSelect(data, row.scam?.id, row.id);
                    }
                },
                @endif
                @if ($assigneeType === 'drafting' && ($userType === 'drafting' || $userType === 'admin'))
                {
                    data: 'drafting_status_id',
                    name: 'drafting_status_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return Action.getDraftingStatusSelect(data, row.id);
                    }
                },
                @endif
                @if ($assigneeType === 'sales' && ($userType === 'sales' || $userType === 'admin'))
                {
                    data: 'scam_sales_status_id',
                    name: 'scam_sales_status_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        // return Action.getScamSalesStatusSelect(data, row.scam?.id, row.id);
                        const selectedStatus = scamStatuses.filter((item) => item.id == data)?.[0] ?? null;
                        return selectedStatus?.title ?? noContentText();
                    },
                    createdCell: function(td, cellData, rowData, rowIndex, colIndex) {
                        const reviewStatus = rowData?.sales_status_record?.review;
                        if(reviewStatus) {
                            $(td).css('background-color', rowData.sales_status_review_color);
                        }
                    }
                },
                @endif
                @if ($assigneeType === 'drafting' && ($userType === 'drafting' || $userType === 'admin'))
                {
                    data: 'scam_drafting_status_id',
                    name: 'scam_drafting_status_id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        // return Action.getScamDraftingStatusSelect(data, row.scam?.id, row.id);
                        const selectedStatus = scamStatuses.filter((item) => item.id == data)?.[0] ?? null;
                        return selectedStatus?.title ?? noContentText();
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
                    data: 'created_at',
                    name: 'created_at',
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
                        return Action.show(data);
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

        $('#records_type_select, #bypassed_select').on('change', function () {
            dtTable.draw();
        });

        $(document).on('app:status_update_with_data_success', function() {
            dtTable.draw(false);
        });

        function setupDtSelects(selector) {
            $('input.dt-select-checkbox').addClass('form-check-input cursor-pointer');
            $('input.dt-select-checkbox').on('change', function() {
                const selectedRows = dtSelectedRows(dtTable);
                pageButtonVisibility('.__bulk_assign_btn', selectedRows.length > 0);
            });
        }

        function assigneeSelectHandler({
            $selectElement,
            type,
            originalAssigneeId
        }) {
            const selectedAssigneeId = $selectElement.val();
            const scam_id = $selectElement.data('scam-id');
            const enquiry_id = $selectElement.data('enquiry-id');
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
                        type,
                        enquiry_id
                    }, function(res) {
                        if (res.success) {
                            toast.open({
                                type: 'success',
                                message: success_message
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

        $('#customer-enquiries-table').on('change', '.sales-assignee-select', function() {
            assigneeSelectHandler({
                $selectElement: $(this),
                type: 'sales',
                originalAssigneeId: $(this).data('sales-assignee')
            });
        });

        $('#customer-enquiries-table').on('change', '.drafting-assignee-select', function() {
            assigneeSelectHandler({
                $selectElement: $(this),
                type: 'drafting',
                originalAssigneeId: $(this).data('drafting-assignee')
            });
        });

        
        @can(Permission::CUSTOMER_ENQUIRY_UPDATE_STATUS)

            $('#customer-enquiries-table').on('change', '.sales-status-select', function() {
                statusSelectHandler({$selectElement: $(this), type: 'sales', originalStatusId: $(this).data('status') });
            });

            $('#customer-enquiries-table').on('change', '.drafting-status-select', function() {
                statusSelectHandler({$selectElement: $(this), type: 'drafting', originalStatusId: $(this).data('status') });
            });
        
        
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
                const enquiry_id = $selectElement.data('enquiry-id');
                const enquiry = dtGetEnquiryById(enquiry_id);
                const statusObj = findObjectInArrayByKey(customerEnquiryStatuses, 'id', selectedStatusId);
              
                if(statusObj?.consider_resolved) {
                    SCAM_STATUS_UPDATE_MODULE.open(enquiry, type);
                    return;
                }

                if(statusObj?.is_remark_required) {
                    ENQUIRY_STATUS_UPDATE_DATA_MODULE.open(enquiry, statusObj, type);
                    return;
                }

                let message = '';
                let success_message = '';
                if (originalStatusId == null) {
                    message = `You are about to set ${type} status of this enquiry.`;
                    success_message = `Status has been set`;
                } else if (selectedStatusId) {
                    message = `You are about to change the ${type} status of this enquiry.`;
                    success_message = `Status has been changed`;
                } else {
                    message = `You are about to remove the ${type} status of this enquiry.`;
                    success_message = `Status has been removed`;
                }

                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-circle',
                    message: message,
                    onConfirm: async function() {
                        const url = Action.changeStatusUrl.replace(':id', enquiry.id);
                        const status_id = $selectElement.val();
                        await $.post(url, {
                            status_id,
                            type
                        }, function(res) {
                            $(document).trigger('app:scam_status_updated', {
                                response: res,
                                request_type: 'normal'
                            });
                            if (res.success) {
                                toast.open({
                                    type: 'success',
                                    message: success_message
                                });
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
        @endcan

        @if ($pms->sales_access)
            // Sales Status Select
            $('#customer-enquiries-table').on('change', '.scam-sales-status-select', function() {
                scamStatusSelectHandler({
                    $selectElement: $(this),
                    type: 'sales',
                    originalStatusId: $(this).data('sales-status'),
                });
            });
        @endif

        @if ($pms->drafting_access)
            // Drafting Status Select
            $('#customer-enquiries-table').on('change', '.scam-drafting-status-select', function() {
                scamStatusSelectHandler({
                    $selectElement: $(this),
                    type: 'drafting',
                    originalStatusId: $(this).data('drafting-status')
                });
            });
        @endif


        FilterModule.registerDatatable(dtTable);
    });

    function scamStatusSelectHandler({
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
            const enquiry_id = $selectElement.data('enquiry-id');
            const scam = dtGetScamById(scam_id);
            const enquiry = dtGetEnquiryById(enquiry_id);

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
                        $(document).trigger('app:scam_status_updated');
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
  </script>
@endpush
