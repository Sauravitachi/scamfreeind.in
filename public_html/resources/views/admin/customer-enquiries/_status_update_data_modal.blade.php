@use(App\Http\Requests\Admin\ChangeCustomerEnquiryStatusRequest)

<div>
    <div class="modal modal-blur fade" id="enq-status-update-data-modal" tabindex="-1" style="display: none;" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <input type="hidden" name="scam_id" value />
                <input type="hidden" name="toast" value="1" />
                <div class="modal-header">
                    <h5 class="modal-title">Submit Required Data</h5>
                    <button type="button" class="btn-close close-modal-btn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-container">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn close-modal-btn me-auto" data-bs-dismiss="modal">Close</button>
                    <x-admin.button class="submitBtn" label='Change Status' />
                </div>
            </div>
        </div>
    </div>
    <div id="status-update-data-form-container-template" style="display: none;">
        <div>
            <form class="data-form" id="esc-status-data-form" method="POST" action="">
                @csrf
                <input type="hidden" class="status_id_inp" name="status_id">
                <input type="hidden" class="type_inp" name="type">
                <div class="row">
                    <x-admin.textarea name='remark' label='Remark' placeholder='Enter remark (max 1000 characters)' required />
                </div>
            </form>
        </div>
    </div>
</div>



@push('script')
    {!! js_validation_custom_event(ChangeCustomerEnquiryStatusRequest::class, '#esc-status-data-form', '#enq-status-update-data-modal', 'validate') !!}

    <script>
        var ENQUIRY_STATUS_UPDATE_DATA_MODULE = {

            refreshOnUpdate: {{ $refreshOnUpdate ?? false ? 'true' : 'false' }},

            formUrl: "{{ route('admin.customer-enquiries.change-status', ':id') }}",


            register: function() {
                this.$modal = $('#enq-status-update-data-modal');
                this.$container = this.$modal.find('.form-container');
                this.$submitBtn = this.$modal.find('button.submitBtn');
                this.formTemplate = $('#status-update-data-form-container-template').html();

                this.$modal.find('.close-modal-btn').off('click').on('click', function() {
                    if(ENQUIRY_STATUS_UPDATE_DATA_MODULE.refreshOnUpdate) {
                        location.reload();
                        return;
                    }
                    ENQUIRY_STATUS_UPDATE_DATA_MODULE.redrawTable();
                });
            },

            open: function(enquiry, statusObj, type) {

                this.$container.html(this.formTemplate);

           
                this.$modal.modal('show');
                this.$form = this.$modal.find('form');

                const formAction = this.formUrl.replace(':id', enquiry.id);
                this.$form.attr('action', formAction);
                this.$form.find('.status_id_inp').val(statusObj.id);
                this.$form.find('.type_inp').val(type);
                
                this.$submitBtn.off('click').on('click', function() {
                    ENQUIRY_STATUS_UPDATE_DATA_MODULE.$form.submit();
                });
                
                ajaxForm('#' + this.$form.attr('id'), {
                    handleToast: true,
                    success: function(res) {
                        if(res.success) {

                            ENQUIRY_STATUS_UPDATE_DATA_MODULE.close();
                            ENQUIRY_STATUS_UPDATE_DATA_MODULE.redrawTable();
                        }
                    }
                });

                this.$modal.trigger('validate');
            },

            close: function() {
                this.$modal.modal('hide');
            },

            redrawTable: function() {
                if (typeof dtTable !== 'undefined' && dtTable) {
                    dtTable.draw(false);
                }
            }


        };

        $(document).ready(function() {
            ENQUIRY_STATUS_UPDATE_DATA_MODULE.register();
        });
    </script>
@endpush