@use(App\Utilities\Html)
@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@php
    $bulkSelectRequired = auth()->user()->canAny([
        Permission::SCAM_LEAD_BULK_DELETE,
        Permission::SCAM_LEAD_BULK_TRANSFER
    ]);
@endphp

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-leads.index'),
    'filters' => true,
    'buttons' => [
        auth()->user()->can(Permission::SCAM_LEAD_BULK_DELETE)
            ? [
                'label' => 'Delete',
                'icon' => 'ti ti-trash',
                'variant' => 'outline-danger',
                'class' => '__bulk_delete_btn',
                'invisible' => true,
            ] : null,
        auth()->user()->can(Permission::SCAM_LEAD_BULK_TRANSFER)
            ? [
                'label' => 'Transfer',
                'icon' => 'ti ti-transfer',
                'variant' => 'outline-info',
                'class' => '__bulk_transfer_btn',
                'invisible' => true,
            ] : null,
        auth()->user()->can(Permission::SCAM_LEAD_CREATE)
            ? ['label' => 'Add new lead', 'icon' => 'ti ti-plus', 'url' => route('admin.scam-leads.create')]
            : null,
    ],
])

@section('filters-body')
    <div class="row">
        <div class="col-12">
            @php($options = $scamTypes->pluck('title', 'id')->toArray())
            <x-admin.select name='filter_scam_type_id' label='Scam Type' class="filter-select2" :options="$options" placeholder="Select" />
        </div>
        <div class="col-12">
            <x-admin.select2-ajax name='filter_scam_source' label='Source' id="filter_scam_source" placeholder='Search Source' :route="route('admin.scam-sources.select-search')" dropdownParent="FilterModule.$filterOffcanvasBody" minimumInputLength="0" paginate />
        </div>
        <div class="col-12">
            <x-admin.select name='filter_has_errors' label='Has Errors?' class="filter-select2" :options="[
                1 => 'Yes',
                0 => 'No'
            ]" placeholder="Select" />
        </div>
        <div class="col-12">
            <x-admin.select name='filter_already_exists' label='Already Exists?' class="filter-select2" :options="[
                1 => 'Yes',
                0 => 'No'
            ]" placeholder="Select" />
        </div>
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='filter_created_at' label='Registered At' placeholder='Select Range' />
        </div>
    </div>
@endsection

@include('admin.layouts.components.select2')
@include('admin.layouts.components.datatable')
@include('admin.layouts.components.datepicker')

@section('content')
    <div class="row">
        <div class="col-12">
            <x-admin.alert variant='danger' icon='ti ti-exclamation-circle' message='Leads with error cannot be transferred into clients list. Records must be first fixed by editing.' />
            @include('admin.layouts.components.datatable-header', [
                'id' => 'users-table',
                'data' => [
                    ['title' => ''],
                    ['title' => Html::icon('ti ti-alert-square-rounded fs-2')],
                    ['title' => Html::icon('ti ti-user fs-2')],
                    ['title' => 'Full Name'],
                    ['title' => 'Email'],
                    ['title' => 'Country'],
                    ['title' => 'Phone Number'],
                    ['title' => 'Scam Type'],
                    ['title' => 'Scam Amount'],
                    ['title' => 'Lead Source'],
                    ['title' => 'Registerd At'],
                    ['title' => 'Action'],
                ],
            ])
        </div>
    </div>
    @include('admin.scam-leads._edit_offcanvas')
    @include('admin.scam-leads._duplicate_leads_offcanvas')
@endsection

@push('script')
    <script>
        var dtTable = null;

        const Action = {
            ...@js([
                'editUrl' => route('admin.scam-leads.edit', ':id'),
                'deleteUrl' => route('admin.scam-leads.destroy', ':id'),
                'transferUrl' => route('admin.scam-leads.transfer', ':id'),
                'bulkDeleteUrl' => route('admin.scam-leads.bulk-delete'),
                'bulkTransferUrl' => route('admin.scam-leads.bulk-transfer'),
                'canEdit' => auth()->user()->can(Permission::SCAM_LEAD_UPDATE),
                'canDelete' => auth()->user()->can(Permission::SCAM_LEAD_DELETE),
                'canTransfer' => auth()->user()->can(Permission::SCAM_LEAD_TRANSFER),
                'canBulkDelete' => auth()->user()->can(Permission::SCAM_LEAD_BULK_DELETE),
                'canBulkTransfer' => auth()->user()->can(Permission::SCAM_LEAD_BULK_TRANSFER),
                'bulkSelectRequired' => $bulkSelectRequired
            ]),
            edit: function(id) {
                if (!Action.canEdit)
                    return '';
                const url = this.editUrl.replace(':id', id);
                return `<a href="javascript:;" data-edit-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-edit text-primary h1"></i></a>`;
            },
            delete: function(id) {
                return Action.canDelete ?
                    `<a href="javascript:;" data-delete-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-trash text-danger h1"></i></a>` :
                    '';
            },
            transfer: function(id) {
                if (!Action.canTransfer)
                    return '';
                const url = this.transferUrl.replace(':id', id);
                return `<a href="javascript:;" data-transfer-id="${id}" class="cursor-pointer mx-1"><i class="ti ti-transfer text-success h1"></i></a>`;
            },
        };
        

        $(document).ready(function() {

            dtTable = $('#users-table').DataTable({
                responsive: true,
                searchDelay: 500,
                processing: true,
                serverSide: true,
                ajax: {
                    url: @js(route('admin.scam-leads.index')),
                    data: function(d) {
                        d = withFilterData(d);
                    }
                },
                order: [
                    [10, 'desc'] // created_at
                ],
                oLanguage: {
                    sLengthMenu: "_MENU_ entries per page",
                },
                select: {
                    style: 'multi',
                    selector: 'td input.dt-select-checkbox'
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        render: DataTable.render.select(),
                        targets: 0
                    },
                    {
                        data: 'errors',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            const errors = data;
                            const errorsCount = errors?.length ?? 0;
                            if (errorsCount <= 0) {
                                const $elem = $(HtmlTag.icon(
                                    'ti ti-check ',
                                    'import-row-success text-white bg-success avatar avatar-xs avatar-rounded fs-4'
                                ));
                                return $elem.outerHtml();
                            }
                            const elem = HtmlTag.span(errorsCount,
                                'bg-danger text-white avatar avatar-xs avatar-rounded');


                            const $elem = $(elem).attr('role', 'button')
                                .attr('onclick',
                                    `showRowErrors(${row['id']})`);

                            return $elem.outerHtml();
                        }
                    },
                    {
                        data: 'existing_customer',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            const user = data;

                            if (!user) {
                                return '';
                            }

                            const icon = HtmlTag.icon('ti ti-user-hexagon',
                                'fs-1 text-primary');

                            const $icon = $(icon)
                                .attr('role', 'button')
                                .attr('onclick',`showExistingCustomer(${row['id']})`);

                            return $icon.outerHtml();
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'country_name',
                        name: 'country_name',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
                        }
                    },
                    {
                        data: 'phone_number',
                        name: 'phone_number',
                        render: function(data, type, row, meta) {

                            const count = row.count ?? 0;

                            const $countElem = count > 1 ? $(HtmlTag.span(count - 1, 'bg-info text-white avatar avatar-xs avatar-rounded ms-2'))
                                .attr('role', 'button')
                                .attr('onclick', `DUPLICATE_LEADS_MODULE.open(${row.id})`).outerHtml() : '';

                            return data + $countElem;
                        }
                    },
                    {
                        data: 'scam_type',
                        name: 'scam_type',
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return data ?? noContentText();
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
                    {
                        data: 'created_at',
                        name: 'scam_leads.created_at',
                        render: function(data, type, row, meta) {
                            return row['created_at_formatted'] ?? noContentText();
                        }
                    },
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, row, meta) {

                            const actionButtonView = row['errors'].length <=0 ? Action.transfer(data) : '';

                            return actionButtonView + Action.edit(data) + Action.delete(data);
                        }
                    },
                ],
            }).on('draw responsive-display', function() {
                setupDtSelects();
            }).on('init', function() {
                setupDtSelects();
            }).on('select', function(e, dt, type, indexes) {
                $(dt.rows(indexes).nodes()).addClass('dt-custom-select');
            }).on('deselect', function(e, dt, type, indexes) {
                $(dt.rows(indexes).nodes()).removeClass('dt-custom-select');
            });

            $("#users-table").on('click', '[data-edit-id]', editLead);
            $("#users-table").on('click', '[data-delete-id]', deleteLead);
            $("#users-table").on('click', '[data-transfer-id]', transferLead);
            
            FilterModule.registerDatatable(dtTable);

            function editLead() {
                EDIT_LEAD_MODULE.open($(this).data('edit-id'));
            }

            Action.canBulkDelete && $('.__bulk_delete_btn').on('click', function() {
                const selectedLeads = dtSelectedRows(dtTable, 'id');
                if (selectedLeads.length <= 0) {
                    toast.open({ type: 'warning', message: 'No records are selected!' });
                    return;
                }
                Popup.askConfirmation({
                    variant: 'danger',
                    icon: 'ti ti-trash',
                    message: `You are about to bulk delete <strong>leads</strong>.<br>If you proceed, you won't be able to revert this.`,
                    onConfirm: async function() {
                        const url = Action.bulkDeleteUrl;
                        await $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                ids: selectedLeads
                            },
                            success: function(response) {
                                if (response.success) {
                                    response.toast && toast.open(response.toast);
                                    dtTable.draw();
                                }
                            },
                            error: function() {
                                toast.open('error', 'Something Went Wrong!');
                            }
                        });
                    }
                });

            });


            Action.canBulkTransfer && $('.__bulk_transfer_btn').on('click', function() {
                const selectedLeads = dtSelectedRows(dtTable, 'id');
                if (selectedLeads.length <= 0) {
                    toast.open({ type: 'warning', message: 'No records are selected!' });
                    return;
                }
                Popup.askConfirmation({
                    variant: 'warning',
                    icon: 'ti ti-alert-triangle',
                    message: `You are about to bulk transfer <strong>leads</strong>.<br>If you proceed, you won't be able to revert this.`,
                    onConfirm: async function() {
                        const url = Action.bulkTransferUrl;
                        await $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                ids: selectedLeads
                            },
                            success: function(response) {
                                if (response.success) {
                                    response.toast && toast.open(response.toast);
                                    dtTable.draw();
                                }
                            },
                            error: function() {
                                toast.open('error', 'Something Went Wrong!');
                            }
                        });
                    }
                });

            });
        });

        function setupDtSelects(selector) {

            $checkbox =  $('input.dt-select-checkbox');
            $checkbox.addClass('form-check-input cursor-pointer');

            if(!Action.bulkSelectRequired) {
                $checkbox.prop('disabled', true)
                return;
            }
            
            $checkbox.on('change', function() {
                const selectedRows = dtSelectedRows(dtTable);
                pageButtonVisibility('.__bulk_delete_btn', selectedRows.length > 0);
                pageButtonVisibility('.__bulk_transfer_btn', selectedRows.length > 0);
            });
        }

        function deleteLead(leadId) {
            const id = typeof leadId === 'number' ? leadId : $(this).data('delete-id');
            Popup.askConfirmation({
                variant: 'danger',
                icon: 'ti ti-trash',
                message: `You are about to delete the <strong>scam lead</strong>.<br>If you proceed, you won't be able to revert this.`,
                onConfirm: async function() {
                    const url = Action.deleteUrl.replace(':id', id);
                    await runAjax({
                        url: url,
                        method: 'DELETE',
                        handleToast: true,
                        success: function(res) {
                            dtTable.draw(false);
                            DUPLICATE_LEADS_MODULE?.refresh();
                        },
                    });
                }
            });
        }

        function transferLead(leadId) {
            const id = typeof leadId === 'number' ? leadId : $(this).data('transfer-id');
            Popup.askConfirmation({
                variant: 'success',
                icon: 'ti ti-transfer',
                message: `You are about to transfer the <strong>scam lead</strong> to main list.`,
                onConfirm: async function() {
                    const url = Action.transferUrl.replace(':id', id);
                    await runAjax({
                        url: url,
                        method: 'POST',
                        handleToast: true,
                        success: function(res) {
                            dtTable.draw(false);
                            DUPLICATE_LEADS_MODULE?.refresh();
                        },
                    });
                }
            });
        }


        function showExistingCustomer (id) {

            let customer = findObjectInArrayByKey(dtAllRows(dtTable), 'id', id)
                    ?.existing_customer;
            
            showExistingCustomerModal(customer);
        }

        function showExistingCustomerModal(customer)
        {
            if (!customer || customer == '')
                return;

            customer = JSON.parse(customer);
            console.log(customer);

            const html = HtmlTag.div(
                HtmlTag.div(`#${customer.track_id ?? ''}`, 'mt-1') +
                HtmlTag.div(`${customer.full_name ?? ''}`, 'mt-1') +
                HtmlTag.div(`${customer.full_phone_number ?? ''}`, 'mt-1') +
                HtmlTag.div(`${customer.email ?? ''}`, 'mt-1') +
                HtmlTag.div(`${customer.country_details ?? ''}`, 'mt-1')
            );

            Popup.alert({
                type: 'primary',
                icon: 'ti ti-user',
                title: 'Customer',
                content: html
            });
        }


        function showRowErrors (id) {

            const errors = findObjectInArrayByKey(dtAllRows(dtTable), 'id', id)
                ?.errors;

            showRowErrorsModal(errors);
        }

        function showRowErrorsModal(errors) {
            if (typeof errors === 'string') {
                try {
                    errors = JSON.parse(errors); // Parse JSON string to an array
                } catch (e) {
                    console.error('Invalid JSON:', errors);
                    return;
                }
            }

            if (!Array.isArray(errors) || errors.length <= 0) return;

            const html = errors
                .map(error =>
                    `<div class="mt-2"><i class="ti ti-exclamation-circle text-danger"></i> ${error}</div>`)
                .join(""); // Join array elements into a single string

            Popup.alert({
                type: 'danger',
                title: 'Row Errors',
                content: html
            });
        }


    </script>
@endpush
