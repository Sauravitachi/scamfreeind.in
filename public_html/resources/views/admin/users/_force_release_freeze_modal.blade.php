@use(App\Http\Requests\Admin\UserForceReleaseFreezeRequest)

<div>
    <div class="modal modal-blur fade" id="force-release-freeze-modal" tabindex="-1" style="display: none;"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form class="modal-content" method="POST" action="" id="force-release-freeze-form">
                <div class="modal-header">
                    <h5 class="modal-title">Force release freeze</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <x-admin.button label="Release" icon='ti ti-lock-open' submit />
                </div>
            </form>
        </div>
    </div>
    <div class="force-release-freeze-body-content" style="display: none;">
        <div>
            <x-admin.input type='number' step='1' name='freeze_disabled_until_hours' label='Release Until (Hours)' placeholder='Enter hours' />
        </div>
    </div>
</div>

@push('script')
    {!! js_validation_custom_event(
        formRequestClass: UserForceReleaseFreezeRequest::class,
        formSelector: '#force-release-freeze-form',
        eventTargetSelector: '#force-release-freeze-modal',
        event: 'run-validation',
    ) !!}
    <script>
        const ForceReleaseFreezeModule = {

            userId: null,

            $modal: null,
            $modalBody: null,
            $form: null,

            submitUrl: null,

            formActionUrl: @js(route('admin.users.force-release-freeze', ':id')),

            prepare: function() {

                if (!UserDetailModule.userId) {
                    return;
                }

                ForceReleaseFreezeModule.userId = UserDetailModule.userId;

                ForceReleaseFreezeModule.$modal = $('#force-release-freeze-modal');
                ForceReleaseFreezeModule.$modalBody = ForceReleaseFreezeModule.$modal.find('.modal-body');

                ForceReleaseFreezeModule.$modalBody.html($('.force-release-freeze-body-content').html());

                ForceReleaseFreezeModule.$form = ForceReleaseFreezeModule.$modal.find(
                    'form#force-release-freeze-form');

                ForceReleaseFreezeModule.$form.attr('action', ForceReleaseFreezeModule.formActionUrl.replace(':id',
                    ForceReleaseFreezeModule.userId));

                ForceReleaseFreezeModule.triggerValidation();

                ForceReleaseFreezeModule.applyFormSubmit();

            },

            open: function() {

                ForceReleaseFreezeModule.prepare();

                ForceReleaseFreezeModule.$modal.modal('show');

            },

            close: function() {

                ForceReleaseFreezeModule.$modal.modal('hide');

            },

            triggerValidation: function() {
                ForceReleaseFreezeModule.$form.validate().destroy();
                ForceReleaseFreezeModule.$modal.trigger('run-validation');
            },

            applyFormSubmit: function() {

                ajaxForm('#force-release-freeze-form', {
                    handleToast: true,
                    showOverlayLoader: true,
                    success: function(res) {
                        ForceReleaseFreezeModule.close();
                        UserDetailModule.refresh();
                    }
                });
            }
        };
    </script>
@endpush
