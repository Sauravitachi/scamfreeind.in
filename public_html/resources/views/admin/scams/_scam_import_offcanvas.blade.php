<div>
    <div class="offcanvas offcanvas-end offcanvas-full" tabindex="-1" id="excel-import-offcanvas">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title">Import scams through file</h2>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-5">
            <div class="upload-area">

            </div>
            <div class="table-view-area mt-3">

            </div>
        </div>
    </div>

    <div id="excel-file-container-html" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div>
                    <div class="row align-items-center">
                        <div class="col-3">
                            <img src="{{ asset('assets/theme/img/icons/cloud-sheet.svg') }}" alt="Sheet Import Icon"
                                class="rounded">
                        </div>
                        <div class="col">
                            <h3 class="card-title mb-1">
                                <a href="#" class="text-reset">{filename}</a>
                            </h3>
                            <div class="text-secondary">
                                {description}
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            <x-admin.checkbox name='unique_phone_number' label='Unique Phone Numbers' :checked="true" />
                        </div>
                        <div class="col">
                            <x-admin.checkbox name='unique_scam_type' label='Unique Scam Type' />
                        </div>
                        <div class="col">
                            <x-admin.checkbox name='unique_scam_amount' label='Unique Scam Amount' />
                        </div>
                        <div class="col">
                            <x-admin.checkbox name='skip_existing_phone_number' label='Skip Existing Phone Numbers' :checked="true" />
                        </div>
                    </div>
                    <div class="row mt-2 justify-content-end">
                        <div class="col-lg-auto">
                            <div class="text-end">
                                <x-admin.button label='Process File' variant='primary' icon='ti ti-automation'
                                    onclick="ExcelImportModule.processFile();" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="excel_import_offcanvas_body_section" style="display: none;">
        <div>
            <a href="{{ asset('downloads/scam-import-sheet-template.xlsx') }}" download>
                <i class="ti ti-download"></i> Download sheet template file
            </a>
            <form class="dropzone mt-3" autocomplete="off" novalidate>
                <div class="fallback">
                    <input name="file" type="file" />
                </div>
            </form>
        </div>
    </div>

    <div id="excel_import_processed_data_table" style="display: none;">
        <div>
            <div class="card">
                <div class="card-body">
                    <x-admin.alert variant='danger' class="import_error_alert" style="display: none;" message='' />
                    <div class="text-end my-3">
                        <x-admin.button label='Import Selected' variant='outline-primary'
                            class="__import_selected_rows_btn" style="display: none;"
                            onclick="ExcelImportModule.import(true);" />
                        <x-admin.button label='Import All' class="__import_all_rows_btn"
                            onclick="ExcelImportModule.import();" />
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="sheet-import-table">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@push('script')
    <script>
        var ExcelImportModule = {

            file: null,
            $offcanvase: null,
            $offcanvasBody: null,
            $uploadArea: null,
            $tableViewArea: null,
            data: null,
            dtTable: null,

            register: function() {
                // ExcelImportModule.open();
                $('.__excel_import_btn').on('click', ExcelImportModule.open);
            },

            prepare: function() {
                ExcelImportModule.file = null;

                ExcelImportModule.$offcanvas = $('#excel-import-offcanvas');
                ExcelImportModule.$offcanvasBody = ExcelImportModule.$offcanvas.find('.offcanvas-body');
                ExcelImportModule.$uploadArea = ExcelImportModule.$offcanvasBody.find('.upload-area');
                ExcelImportModule.$tableViewArea = ExcelImportModule.$offcanvasBody.find('.table-view-area');

                // rendering body
                ExcelImportModule.$uploadArea.html($('#excel_import_offcanvas_body_section').html());
                ExcelImportModule.$tableViewArea.empty();
            },

            open: function() {

                ExcelImportModule.prepare();

                ExcelImportModule.$uploadArea.find('.dropzone').dropzone({
                    url: '/',
                    maxFiles: 1,
                    maxFilesize: 10,
                    acceptedFiles: ".xlsx,.csv",
                    addRemoveLinks: true,
                    autoProcessQueue: false,
                    init: function() {
                        this.on("addedfile", function() {
                            const file = this.files[0];
                            delay(function() {
                                console.log(file);
                                if (file.accepted) {
                                    ExcelImportModule.file = file;
                                    ExcelImportModule.fileUploaded();
                                }
                            }, 50);
                        });

                        this.on("error", function(file, errorMessage) {
                            this.removeFile(file);
                            toast.open({
                                type: 'error',
                                message: errorMessage || 'Invalid file!'
                            });
                        });
                    }
                });

                ExcelImportModule.$offcanvas.offcanvas('show');
            },

            close: function() {
                ExcelImportModule.$offcanvas.offcanvas('hide');
            },

            fileUploaded: function() {

                const file = ExcelImportModule.file;

                if (!file)
                    return;

                ExcelImportModule.$offcanvasBody.find('.dropzone').remove();

                const fileTypeText = ExcelImportModule.getFileType();


                let fileAreaHtml = $('#excel-file-container-html').html().replace('{filename}', file.name).replace(
                    '{description}', fileTypeText);

                ExcelImportModule.$uploadArea.html(fileAreaHtml);

            },

            getFileType() {
                if (!ExcelImportModule.file)
                    return '';
                const file = ExcelImportModule.file;
                let fileType = '';
                if (file.name.endsWith('.xlsx')) {
                    fileType = 'Excel file';
                } else if (file.name.endsWith('.csv')) {
                    fileType = 'CSV file';
                }
                return fileType;
            },

            processFile: function() {
                if (!ExcelImportModule.file) {
                    alert('Invalid Action Performed');
                    return;
                }

                const formData = new FormData();
                formData.append('file', ExcelImportModule.file);
                ['unique_phone_number', 'unique_scam_type', 'unique_scam_amount', 'skip_existing_phone_number'].forEach(function(key) {
                    formData.append(key, $(`input[name="${key}"]`).prop('checked') ? 1 : 0);
                });

                runAjax({
                    url: @js(route('admin.scams.process-import-file')),
                    method: 'POST',
                    data: formData,
                    showOverlayLoader: true,
                    handleToast: true,
                    ajaxOptions: {
                        processData: false,
                        contentType: false,
                    },
                    success: function(res) {
                        console.log(res);
                        if (res?.data) {
                            ExcelImportModule.data = res.data;
                            ExcelImportModule.$tableViewArea.html(
                                $('#excel_import_processed_data_table').html()
                            );
                            ExcelImportModule.dataTableInit(res.data);

                            // error count
                            const totalErrorFields = res.data.filter(item => Array.isArray(item
                                    .errors) &&
                                item.errors.length > 0).length;

                            // error alert visibility
                            if (totalErrorFields > 0) {
                                $('.import_error_alert').find('.message').html(
                                    HtmlTag.div(
                                        `There are ${totalErrorFields} row(s) exists with errors.`
                                    ) +
                                    HtmlTag.div(
                                        `Rows with errors will be skipped during imports.`)
                                );
                                $('.import_error_alert').show();
                            } else {
                                $('.import_error_alert').hide();
                            }
                        }
                    }
                });

            },


            dataTableInit: function(data) {

                $dtTableSelector = $('#sheet-import-table');

                function setupDtSelects(selector) {
                    $dtTableSelector.find('input.dt-select-checkbox').addClass('form-check-input cursor-pointer');
                    $('input.dt-select-checkbox').on('change', function() {
                        const selectedRows = dtSelectedRows(ExcelImportModule.dtTable);

                        if (selectedRows.length > 0) {
                            $('.__import_selected_rows_btn').show();
                            $('.__import_all_rows_btn').hide();
                        } else {
                            $('.__import_selected_rows_btn').hide();
                            $('.__import_all_rows_btn').show();
                        }
                    });
                }

                ExcelImportModule.dtTable = $dtTableSelector.DataTable({
                    responsive: false,
                    oLanguage: {
                        sLengthMenu: "_MENU_ entries per page",
                    },
                    select: {
                        style: 'multi',
                        selector: 'td input.dt-select-checkbox'
                    },
                    order: [
                        [1, 'asc'] // created_at
                    ],
                    data: data,
                    columns: [{
                            data: 'index',
                            orderable: false,
                            searchable: false,
                            render: DataTable.render.select(),
                            targets: 0
                        }, {
                            data: 'index',
                            title: 'Sr.'
                        },
                        {
                            data: 'errors',
                            title: HtmlTag.icon('ti ti-alert-square-rounded', 'fs-2'),
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
                                        `ExcelImportModule.showRowErrors(${row['index']})`);

                                return $elem.outerHtml();
                            }
                        },
                        {
                            data: 'existing_customer',
                            title: HtmlTag.icon('ti ti-user', 'fs-2'),
                            render: function(data, type, row, meta) {
                                const user = data;

                                if (!user) {
                                    return '';
                                }

                                const icon = HtmlTag.icon('ti ti-user-hexagon',
                                    'fs-1 text-primary');

                                const $icon = $(icon)
                                    .attr('role', 'button')
                                    .attr('onclick',
                                        `ExcelImportModule.showExistingCustomer(${row['index']})`);

                                return $icon.outerHtml();
                            }
                        },
                        {
                            data: 'name',
                            title: 'Name',
                            render: function(data, type, row, meta) {
                                return data ?? noContentText();
                            }
                        },
                        {
                            data: 'phone_number',
                            title: 'Phone Number',
                            render: function(data, type, row, meta) {
                                return data ?? noContentText();
                            }
                        },
                        {
                            data: 'email',
                            title: 'Email',
                            render: function(data, type, row, meta) {
                                return data ?? noContentText();
                            }
                        },
                        {
                            data: 'country',
                            title: 'Country',
                            render: function(data, type, row, meta) {
                                if (data && data.name && data.emoji) {
                                    return `${data.emoji} ${data.name}`;
                                }
                                return noContentText();
                            }
                        },
                        {
                            data: 'scamType',
                            title: 'Scam Type',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.title}` : noContentText();
                            }
                        },
                        {
                            data: 'scam_amount_formatted',
                            title: 'Scam Amount',
                            render: function(data, type, row, meta) {
                                return data ?? noContentText();
                            }
                        },
                        {
                            data: 'salesAssignee',
                            title: 'Sales Assignee',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.name}` : noContentText();
                            }
                        },
                        {
                            data: 'salesStatus',
                            title: 'Sales Status',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.title}` : noContentText();
                            }
                        },
                        {
                            data: 'draftingAssignee',
                            title: 'Drafting Assignee',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.name}` : noContentText();
                            }
                        },
                        {
                            data: 'draftingStatus',
                            title: 'Drafting Status',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.title}` : noContentText();
                            }
                        },
                        {
                            data: 'serviceAssignee',
                            title: 'Service Assignee',
                            render: function(data, type, row, meta) {
                                return data ? `[${data.id}] ${data.name}` : noContentText();
                            }
                        }, {
                            data: 'id',
                            title: 'Action',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                const $icon = $(HtmlTag.icon('ti ti-trash',
                                        'bg-danger text-white avatar avatar-xs avatar-rounded fs-3 p-3'
                                    )).attr('role', 'button')
                                    .attr('onclick',
                                        `ExcelImportModule.deleteRow(${meta.row})`);
                                return $icon.outerHtml();
                            }
                        }
                    ]
                }).on('draw responsive-display', function() {
                    setupDtSelects();
                });
                setupDtSelects();


            },

            deleteRow: function(rowIndex) {
                const table = ExcelImportModule.dtTable;
                const row = table.row(rowIndex);
                row.remove().draw();
            },

            showRowErrors: function(index) {
                const errors = findObjectInArrayByKey(ExcelImportModule.data, 'index', index)
                    ?.errors;
                if (errors.length <= 0)
                    return;

                const html = errors
                    .map(error =>
                        `<div class="mt-2"><i class="ti ti-exclamation-circle text-danger"></i> ${error}</div>`);

                Popup.alert({
                    type: 'danger',
                    title: 'Row Errors',
                    content: html
                });
            },
            showExistingCustomer: function(index) {
                const customer = findObjectInArrayByKey(ExcelImportModule.data, 'index', index)
                    ?.existing_customer;

                if (!customer)
                    return;

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

            },
            import: function(selectedOnly = false) {
                if (!ExcelImportModule.dtTable)
                    return;

                const records = selectedOnly ? dtSelectedRows(ExcelImportModule.dtTable) : dtAllRows(
                    ExcelImportModule.dtTable);

                const dataArray = records.filter(item => Array.isArray(item.errors) && item.errors.length === 0)
                    .map(function(record) {
                        return {
                            name: record.name,
                            phone_number: record.phone_number,
                            email: record.email,
                            country_code: record.country_code,
                            scam_type: record.scamType?.id ?? null,
                            scam_amount: record.scam_amount,
                            sales_assignee: record.salesAssignee?.id ?? null,
                            drafting_assignee: record.draftingAssignee?.id ?? null,
                            service_assignee: record.serviceAssignee?.id ?? null,
                            sales_status: record.salesStatus?.id ?? null,
                            drafting_status: record.draftingStatus?.id ?? null
                        };
                    });

                if (dataArray.length <= 0) {
                    return Popup.alert({
                        icon: 'ti ti-alert-triangle mb-4',
                        type: 'danger',
                        title: 'No Valid data to import!',
                        content: HtmlTag.span('Select valid data for import which doesnt have any errors.',
                            'text-secondary')
                    });
                }


                runAjax({
                    url: @js(route('admin.scams.import')),
                    method: 'POST',
                    data: {
                        data: dataArray
                    },
                    showOverlayLoader: true,
                    handleToast: true,
                    success: function(res) {
                        ExcelImportModule.close();
                        dtTable.draw();
                    },
                });

            }
        };

        $(document).ready(function() {
            ExcelImportModule.register();
        });
    </script>
@endpush
