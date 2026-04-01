@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

<div class="modal modal-blur fade" id="status-update-data-modal" tabindex="-1" style="display: none;" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

@push('script')

    <script>
        var STATUS_UPDATE_DATE_MODULE = {

            uploadedFiles: [],

            register: function() {

                this.$modal = $('#status-update-data-modal');
                this.$modalBody = this.$modal.find('.modal-body');
                this.$submitButton = this.$modalBody.find('button.submit-btn');

                this.loaderTemplate = $(Loader.centerSpinnerLoader('Loading form')).css('height', '300px').removeClass('h-100').outerHtml();

                this.$modal.find('button.btn-close').on('click', function() {
                    $(document).trigger('app:status_data_update_modal_closed');
                });

                $(document).on('app:status-update-data-modal.open', function(e, data) {
                    STATUS_UPDATE_DATE_MODULE.open(data.scamId, data.statusId);
                });
            },

            open: function(scamId, statusId) {
                this.scamId = scamId;
                this.statusId = statusId;

                this.uploadedFiles = [];

                
                this.$modalBody.html(this.loaderTemplate);
                this.$modal.modal('show');

                this.ajax();

            },

            close: function() {
                this.$modal.modal('hide');
            },

            ajax: function() {

                let url = "{{ route('admin.scams.update-status-data-form', [':scam', ':status']) }}";

                url = url.replace(':scam', this.scamId).replace(':status', this.statusId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(res) {
                        STATUS_UPDATE_DATE_MODULE.renderForm(res);
                        STATUS_UPDATE_DATE_MODULE.initializeFormPlugins(res);
                    }
                });

            },

            renderForm: function (formResponse) {
                STATUS_UPDATE_DATE_MODULE.$modalBody.html(formResponse.html);
            },

            initializeFormPlugins: function(formResponse) {

                initSelect2(this.$modalBody.find('select.select2'), { dropdownParent: this.$modalBody });

                this.$modalBody.find(".datetime_picker").flatpickr({
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: false,
                });

                this.$modalBody.find('.dropzone').dropzone({
                    url: @js(route('admin.upload-file')),
                    maxFilesize: 10,
                    acceptedFiles: ".jpg,.jpeg,.png,.gif,.webp,.bmp,.tiff,.avif,.webp,.xlsx,.xls,.csv,.docx,.pdf,.ppt,.pptx,.txt,.rtf,.odt,.csv",
                    addRemoveLinks: true,
                    init: function() {
                        this.on('removedfile', function(file) {
                            const fileData = JSON.parse(file.xhr.response)?.data;
                            if (fileData) {
                                STATUS_UPDATE_DATE_MODULE.uploadedFiles = STATUS_UPDATE_DATE_MODULE.uploadedFiles.filter(item => item.id !==
                                    fileData.id);

                                $('#status-data-update-form')
                                    .find(`input[name="files[]"][data-file-id="${fileData.id}"]`)
                                    .remove();
                            }
                        });
                        this.on('sending', function(file, xhr, formData) {
                            STATUS_UPDATE_DATE_MODULE.$submitButton.prop('disabled', true);
                            formData.append("_token", $('meta[name="_token"]').attr("content"));
                            formData.append('file_directory', @js(\App\Constants\FileDirectory::STATUS_FILES->value));
                        });
                        this.on('success', function(file, res) {
                            const fileData = res.data;
                            STATUS_UPDATE_DATE_MODULE.uploadedFiles.push(fileData);
                            
                            const input = $('<input>')
                                    .attr('type', 'hidden')
                                    .attr('name', 'files[]')
                                    .attr('data-file-id', fileData.id)
                                    .val(fileData.id);

                            $('form#status-data-update-form').append(input);
                        });
                        this.on('complete', function(file) {
                            STATUS_UPDATE_DATE_MODULE.$submitButton.prop('disabled', false);
                        });
                    }
                });

                $(document).off('paste.pasteImageHandler').on('paste.pasteImageHandler', function(event) {
                    const clipboardData = event.originalEvent.clipboardData;
                    const items = clipboardData.items;
                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        if (item.kind === 'file') {
                            const file = item.getAsFile();
                            const dz = Dropzone.forElement(STATUS_UPDATE_DATE_MODULE.$modalBody.find('.dropzone')[0]);
                            dz.addFile(file);
                        }
                    }
                });

                $('#status_update_registration_amount_select').on('change', function() {
                    const selectedValue = $(this).val();

                    if (!selectedValue) {
                        return;
                    }

                    const $valueContainer = $('.status_update_registration_amount_selected_values');
                    const text = $(this).find(`option[value="${selectedValue}"]`).text();

                    const wrapper = $(`
                        <div class="selected-badge-wrapper me-2 mb-1 d-inline-block" data-value="${selectedValue}">
                            <span class="badge bg-blue text-blue-fg d-inline-flex align-items-center">
                                ${text}
                                <button type="button" class="ms-2 border-0 bg-transparent p-0 unselect-btn" data-value="${selectedValue}">
                                    <i class="ti ti-x text-white"></i>
                                </button>
                            </span>
                            <input type="hidden" name="registration_amount[]" value="${selectedValue}">
                        </div>
                    `);

                    $valueContainer.append(wrapper);
                    $(this).val(null).trigger('change');
                });

                $(document).on('click', '.unselect-btn', function() {
                    $(this).closest('.selected-badge-wrapper').remove();
                });

                const registrations = formResponse?.data?.values?.registrations || [];

                if (registrations.length > 0) {
                    // Set Select2 selected values at once
                    // $('#status_update_registration_amount_select').val(registrations).trigger('change.select2');

                    const $valueContainer = $('.status_update_registration_amount_selected_values');

                    // Append badges manually for each selected value
                    registrations.forEach(function(reg) {
                        const text = $('#status_update_registration_amount_select').find(`option[value="${reg.scam_registration_amount.id}"]`).text();

                        const wrapper = $(`
                            <div class="selected-badge-wrapper me-2 mb-1 d-inline-block" data-value="${reg.scam_registration_amount.id}">
                                <span class="badge bg-blue text-blue-fg d-inline-flex align-items-center">
                                    ${reg.scam_registration_amount.title}
                                    <button type="button" class="ms-2 border-0 bg-transparent p-0 unselect-btn" data-value="${reg.scam_registration_amount.id}">
                                        <i class="ti ti-x text-white"></i>
                                    </button>
                                </span>
                                <input type="hidden" name="registration_amount[]" value="${reg.scam_registration_amount.id}">
                            </div>
                        `);

                        $valueContainer.append(wrapper);
                    });

                    // Clear select input so user can select new values
                    $('#status_update_registration_amount_select').val(null).trigger('change.select2');
                }


                ajaxForm('#status-data-update-form', {
                    handleToast: true,
                    success: function(res) {
                        if(res.success) {
                            STATUS_UPDATE_DATE_MODULE.close();
                            $(document).trigger('app:status_update_with_data_success');
                        }
                    }
                });
                
            }

        };

        $(document).ready(function() {
            STATUS_UPDATE_DATE_MODULE.register();
        });
    </script>
@endpush