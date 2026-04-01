@use(App\Enums\ScamStatusReview)

<div class="modal modal-blur fade" id="sales-status-reject-modal" tabindex="-1" style="display: none;" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <form class="modal-content" method="POST" action="" id="sales-status-reject-form">
            <input type="hidden" name="review" value="{{ ScamStatusReview::REJECTED }}">
            <input type="hidden" name="type">
            <div class="modal-header">
                <h5 class="modal-title">Reject Case Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="reject-status-card-container">
                </div>
            </div>
            <div class="modal-footer">
                <x-admin.button label="Reject" variant='danger' icon='ti ti-cancel' submit />
            </div>
        </form>
    </div>
</div>
<div id="reject-status-card-template" style="display: none;">
    <x-admin.textarea name='review_remark' label='Remark' placeholder='Enter remarks (max 2000 characters)' rows='4' required />
</div>