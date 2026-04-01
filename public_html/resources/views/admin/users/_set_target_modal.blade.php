<div class="modal fade" id="set-target-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-target me-2"></i>Set Sales Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="set-target-form">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="target-user-id">
                    
                    <div class="mb-3">
                        <label class="form-label required">Target Amount</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" required placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Period Type</label>
                        <select class="form-select" name="period_type" required>
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Starts At</label>
                            <input type="date" class="form-control" name="starts_at" required value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Ends At</label>
                            <input type="date" class="form-control" name="ends_at" required value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <i class="ti ti-device-floppy me-2"></i>Save Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    const SetTargetModule = {
        modal: $('#set-target-modal'),
        form: $('#set-target-form'),
        open: function(userId) {
            this.form.trigger('reset');
            $('#target-user-id').val(userId);
            this.modal.modal('show');
        },
        init: function() {
            this.form.on('submit', function(e) {
                e.preventDefault();
                const userId = $('#target-user-id').val();
                const url = Action.setTargetUrl.replace(':id', userId);
                const formData = $(this).serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            SetTargetModule.modal.modal('hide');
                            toast.open({
                                type: 'success',
                                message: res.message || 'Target updated successfully!'
                            });
                            if (typeof dtTable !== 'undefined') dtTable.draw(false);
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.message || 'Something went wrong';
                        toast.open('error', error);
                    }
                });
            });
        }
    };

    $(document).ready(() => SetTargetModule.init());
</script>
@endpush
