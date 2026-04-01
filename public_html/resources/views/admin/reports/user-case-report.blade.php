@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(App\Enums\ScamAssigneeType)

@php
    $assigneeTypesSelectArray = [
        ScamAssigneeType::SALES->value => ScamAssigneeType::SALES->label(),
        ScamAssigneeType::DRAFTING->value => ScamAssigneeType::DRAFTING->label(),
    ];
@endphp

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.reports.user-case-report'),
])

@include('admin.layouts.components.datatable', [
    'fullFeatures' => true
])
@include('admin.layouts.components.select2')
@include('admin.layouts.components.datepicker')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
@endpush

@section('content')

    <div class="card mb-3">
        <div class="card-body">
            <h3 class="fw-normal card-title">Generate User Case Report</h3>

            <form action="{{ route('admin.reports.user-case-report') }}" method="GET" id="user-case-generate-form">

                <div class="row">
                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-12">
                        <x-admin.select name='assignee_type' label='Assignee Type' class="select2" :options="$assigneeTypesSelectArray" />
                    </div>
                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-12">
                        <x-admin.select name='assignee_status' label='Active/Inactive Assignees' class="select2" :options="[1 => 'Active', 0 => 'InActive']" placeholder='Select' />
                    </div>
                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-12">
                        <x-admin.input class="date_range_picker" name='assigned_at' label='Assigned At' placeholder='Select Range' />
                    </div>
                    <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6 col-12">
                        <x-admin.input class="date_range_picker" name='status_updated_at' label='Status Updated At' placeholder='Select Range' />
                    </div>
                </div>
                <div class="text-end">
                    <x-admin.button label='Generate Report' icon='ti ti-report' submit/>
                </div>
            </form>
        </div>
    </div>

    <div id="user-case-report-table-container">

    </div>

    <div id="user-case-report-table-template" style="display: none;">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered report-table" style="width:100%">
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')

    
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

    <script>
        const USER_CASE_REPORT = {

            filter: {
                assigned_at: null,
                status_updated_at: null
            },

            register: function() {

                USER_CASE_REPORT.$container = $('#user-case-report-table-container');

                USER_CASE_REPORT.templateHtml = $('#user-case-report-table-template').html();

            },

            render: function(data) {
                
                USER_CASE_REPORT.assigneeType = data.assigneeType;

                USER_CASE_REPORT.$container.html(USER_CASE_REPORT.templateHtml);

                const $table = USER_CASE_REPORT.$container.find('table.report-table').DataTable({
                    scrollX: true, // Enables horizontal scrolling
                    autoWidth: false, // Prevents automatic width shrinking
                    searching: false,
                    paging: true,
                    layout: {
                        topStart: 'pageLength',
                        topEnd: {
                            buttons: ['copy', 'csv', 'excel', 'print']
                        }
                    },
                    fixedColumns: {
                        leftColumns: 1
                    },
                    pageLength: 50,
                    processing: true,
                    serverSide: false,
                    columns: data.columns.map((col, index) => ({
                        ...col,
                        createdCell: function(td, cellData, rowData, row, colIdx) {
                            if (colIdx === 0) { // Target the first column
                                $(td).addClass('fw-bold');
                            } else {
                                const id = rowData[data.columns[0].data]?.id;
                                const columnName = data.columns[colIdx].name;

                                $(td).addClass('cursor-pointer').on('click', function() {
                                    USER_CASE_REPORT.handleCellClick(id, columnName);
                                });
                            }
                        }
                    })),
                    data: data.rows,
                    createdRow: function(row, rowData, rowIndex) {
                        const numberValues = [];

                        // Loop through column definitions, skip the first column (index 0)
                        data.columns.forEach((col, colIndex) => {
                            if (colIndex === 0) return; // Skip status column

                            const value = rowData[col.data];
                            const intValue = parseInt(value, 10);

                            if (!isNaN(intValue)) {
                                numberValues.push({ colIndex, value: intValue });
                            }
                        });

                        if (numberValues.length > 0) {
                            const max = Math.max(...numberValues.map(obj => obj.value));

                            numberValues.forEach(({ colIndex, value }) => {
                                if (value !== 0 && value === max) {
                                    $('td', row).eq(colIndex).addClass('bg-secondary text-white');
                                }
                            });

                            // ✅ Append sum to the first column
                            const total = numberValues.reduce((sum, obj) => sum + obj.value, 0);
                            const $firstCell = $('td', row).eq(0);
                            const originalContent = rowData[data.columns[0].data]?.title || '';
                            $firstCell.text(`${originalContent} (${total})`);
                        }
                    }

                });

            },

            handleCellClick: function(statusId, userId) {
                const type = USER_CASE_REPORT.assigneeType; // 'sales' or 'drafting'

                // Get base route from Laravel
                const baseUrl = '{!! route("admin.scams.index") !!}';

                // Construct query param keys dynamically
                const statusKey = `${type}_status_id`;
                const assigneeKey = `${type}_assignee_id`;

                // Build full URL
                let url = baseUrl;
                
                url += `?${statusKey}=${statusId ?? -1}&${assigneeKey}=${userId}`;

                if(USER_CASE_REPORT.filter.assigned_at) {
                    url += `&${type}_assigned_at=${USER_CASE_REPORT.filter.assigned_at}`;
                }
                if(USER_CASE_REPORT.filter.status_updated_at) {
                    url += `&${type}_status_updated_at=${USER_CASE_REPORT.filter.status_updated_at}`;
                }

                window.open(url, '_blank');
            },

            updateFilterData: function() {
                $form = $('#user-case-generate-form');
                USER_CASE_REPORT.filter.assigned_at = trimOrNull($form.find('input[name="assigned_at"]')?.val());
                USER_CASE_REPORT.filter.status_updated_at = trimOrNull($form.find('input[name="status_updated_at"]')?.val());
            }

        };

        $(document).ready(function() {

            USER_CASE_REPORT.register();

            ajaxForm('#user-case-generate-form', {
                beforeSend: function() {
                    overlayLoader.show();
                },
                success: function(res) {
                    if(res.success) {
                        const data = res.data;
                        USER_CASE_REPORT.render(res.data);
                        USER_CASE_REPORT.updateFilterData();
                    }
                },
                complete: function() {
                    overlayLoader.hide();
                },
            });

        });
    </script>
@endpush