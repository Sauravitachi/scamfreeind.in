@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

<div class="modal modal-blur fade" id="create-escalation-modal" tabindex="-1" style="display: none;" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form class="modal-content" action="{{ route('admin.escalations.store') }}" method="POST"
            id="create-escalation-form">
            <input type="hidden" name="scam_id" value />
            <input type="hidden" name="toast" value="1" />
            <div class="modal-header">
                <h5 class="modal-title">Make Escalation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                <x-admin.button label="Escalate" icon='ti ti-plus' submit />
            </div>
        </form>
    </div>
</div>
<div id="create-escalation-form-container" style="display: none;">
    <div class="row">
        <div class="col-12">
            @php($options = \App\Enums\EscalationType::selectArray())
            <x-admin.select name='type' label='Type' class="type-select2" :options="$options" placeholder="Select"
                disablePlaceholder required />
        </div>
        <div class="col-12">
            <x-admin.textarea name='message' label='Message' placeholder='Enter message (max 1000 characters)'
                rows='4' required />
        </div>
        <div class="col-12">
            <x-admin.input name='file' label='File' type='file' />
        </div>
    </div>
</div>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\EscalationRequest::class, '#create-escalation-form') !!}
    <script>
        function showCreateEscalationModal(scamId) {
            if (!scamId)
                return;

            close_all_modals();

            const $modal = $('#create-escalation-modal');
            const $modalBody = $modal.find('.modal-body');

            $modal.find('input[name="scam_id"]').val(scamId);
            $modal.modal('show');

            const formHtml = $('#create-escalation-form-container').html();

            $modalBody.html(formHtml);

            initSelect2($modal.find('.type-select2'), {
                dropdownParent: $modalBody
            });
        }
    </script>
@endpush
