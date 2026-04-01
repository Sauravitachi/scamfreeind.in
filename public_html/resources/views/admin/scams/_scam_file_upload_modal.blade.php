<div>
    <div class="modal modal-blur fade" id="upload-scam-files-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <form class="modal-content" method="POST" action="" id="upload-scam-files-form">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Case Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="file-card-container">
                    </div>
                </div>
                <div class="modal-footer">
                    <x-admin.button class="me-auto" label='Add Field' icon='ti ti-plus' variant='outline-primary'
                        onclick="ScamFileUploadModule.addField();" />
                    <x-admin.button label="Upload" icon='ti ti-device-floppy' submit />
                </div>
            </form>
        </div>
    </div>
    <div id="file-card-template" style="display: none;">
        <div class="card file-card mb-3">
            <div class="card-body">
                <div class="d-flex">
                    <h3>
                        File #<span class="index"></span>
                    </h3>
                    <i class="del-btn ti ti-trash ms-auto fs-3 bg-danger text-white avatar avatar-sm avatar-rounded"
                        role="button" style="display: none;">
                    </i>
                </div>
                <x-admin.input type='file' class="file-input" name='' required />
                <x-admin.textarea class='resize-none' class="message-input" name='' rows='3'
                    placeholder='Enter message (max 1000 characters)' />
            </div>
        </div>
    </div>
</div>

@push('script')
    {!! js_validation_custom_event(
        formRequestClass: \App\Http\Requests\Admin\UploadScamFilesRequest::class,
        formSelector: '#upload-scam-files-form',
        eventTargetSelector: '#upload-scam-files-modal',
        event: 'run-validation',
    ) !!}
    <script>
        const ScamFileUploadModule = {

            caseId: null,

            $modal: null,
            $modalBody: null,
            $fileCardContainer: null,
            $form: null,

            submitUrl: null,

            totalFields: 0,

            fieldLimit: 5,

            formActionUrl: @js(route('admin.scams.upload-scam-files', ':id')),

            prepare: function() {

                if (!ScamDetailModule.scamId) {
                    return;
                }

                ScamFileUploadModule.totalFields = 0;

                ScamFileUploadModule.caseId = ScamDetailModule.scamId;

                ScamFileUploadModule.$modal = $('#upload-scam-files-modal');
                ScamFileUploadModule.$modalBody = ScamFileUploadModule.$modal.find('.modal-body');
                ScamFileUploadModule.$fileCardContainer = ScamFileUploadModule.$modalBody.find(
                    '.file-card-container');

                ScamFileUploadModule.$form = ScamFileUploadModule.$modal.find('form#upload-scam-files-form');

                ScamFileUploadModule.submitUrl = ScamFileUploadModule.formActionUrl.replace(':id',
                    ScamFileUploadModule.caseId);

                ScamFileUploadModule.$fileCardContainer.empty();

                ScamFileUploadModule.addField();

                ScamFileUploadModule.$form.attr('action', ScamFileUploadModule.formActionUrl.replace(':id',
                    ScamFileUploadModule.caseId));

                ScamFileUploadModule.applyFormSubmit();

            },

            open: function() {

                ScamFileUploadModule.prepare();

                ScamFileUploadModule.$modal.modal('show');

            },

            close: function() {

                ScamFileUploadModule.$modal.modal('hide');

            },

            addField: function() {
                if (ScamFileUploadModule.totalFields >= ScamFileUploadModule.fieldLimit) {
                    toast.open({
                        type: 'warning',
                        message: `Maximum ${ScamFileUploadModule.fieldLimit} files can be uploaded at once.`
                    });
                    return;
                }
                ScamFileUploadModule.$fileCardContainer.append(ScamFileUploadModule.getNewFileCardHtml());
                ScamFileUploadModule.totalFields++;
                ScamFileUploadModule.indexFields();
                ScamFileUploadModule.triggerValidation();
            },

            removeField: function(index) {
                if (!index)
                    return;
                $field = ScamFileUploadModule.$fileCardContainer.find(`[data-index="${index}"]`);
                if ($field) {
                    $field.remove();
                    ScamFileUploadModule.totalFields--;
                    ScamFileUploadModule.indexFields();
                    ScamFileUploadModule.triggerValidation();
                }
            },

            getNewFileCardHtml: function() {
                return $('#file-card-template').html();
            },

            indexFields: function() {

                const $fields = ScamFileUploadModule.$fileCardContainer.find('.file-card');

                $fields.each(function(index, field) {
                    $field = $(field);
                    const _index = index + 1;
                    $field.attr('data-index', _index);
                    $field.find('.index').html(_index);
                    $field.find('.del-btn')
                        .attr('onclick', `ScamFileUploadModule.removeField(${_index});`);

                    $field.find('.file-input').attr('name', `files[${_index}]`);
                    $field.find('.message-input').attr('name', `messages[${index}]`);

                    $field.find('.del-btn')?.show();
                });

                if ($fields.length === 1) {
                    $($fields[0])?.find('.del-btn')?.hide();
                }

            },

            triggerValidation: function() {
                ScamFileUploadModule.$form.validate().destroy();
                ScamFileUploadModule.$modal.trigger('run-validation');
            },

            applyFormSubmit: function() {

                ajaxForm('#upload-scam-files-form', {
                    handleToast: true,
                    showOverlayLoader: true,
                    success: function(res) {
                        ScamFileUploadModule.close();
                        ScamDetailModule.refresh();
                    }
                });
            }
        };
    </script>
@endpush
