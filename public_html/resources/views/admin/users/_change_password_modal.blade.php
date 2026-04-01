@use(App\Http\Requests\Admin\ChangeAccountPasswordRequest)

<div>
    <div class="modal modal-blur fade" id="change-user-password-modal" tabindex="-1" style="display: none;"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <form class="modal-content" method="POST" action="" id="change-user-password-form">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Account Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <x-admin.button label="Upload" icon='ti ti-device-floppy' submit />
                </div>
            </form>
        </div>
    </div>
    <div class="change-password-body-content" style="display: none;">
        <div>
            <x-admin.input type='password' name='new_password' label='New Password' placeholder='Enter new password' />
            <x-admin.input type='password' name='confirm_new_password' label='Confirm New Password'
                placeholder='Confirm new password' />
            <input type="hidden" name="super" value="1">
        </div>
    </div>
</div>

@push('script')
    {!! js_validation_custom_event(
        formRequestClass: ChangeAccountPasswordRequest::class,
        formSelector: '#change-user-password-form',
        eventTargetSelector: '#change-user-password-modal',
        event: 'run-validation',
    ) !!}
    <script>
        const ChangeUserPasswordModule = {

            userId: null,

            $modal: null,
            $modalBody: null,
            $form: null,

            submitUrl: null,

            formActionUrl: @js(route('admin.users.change-password', ':id')),

            prepare: function() {

                if (!UserDetailModule.userId) {
                    return;
                }

                ChangeUserPasswordModule.userId = UserDetailModule.userId;

                ChangeUserPasswordModule.$modal = $('#change-user-password-modal');
                ChangeUserPasswordModule.$modalBody = ChangeUserPasswordModule.$modal.find('.modal-body');

                ChangeUserPasswordModule.$modalBody.html($('.change-password-body-content').html());

                ChangeUserPasswordModule.$form = ChangeUserPasswordModule.$modal.find(
                    'form#change-user-password-form');

                ChangeUserPasswordModule.$form.attr('action', ChangeUserPasswordModule.formActionUrl.replace(':id',
                    ChangeUserPasswordModule.userId));

                ChangeUserPasswordModule.triggerValidation();

                ChangeUserPasswordModule.applyFormSubmit();

            },

            open: function() {

                ChangeUserPasswordModule.prepare();

                ChangeUserPasswordModule.$modal.modal('show');

            },

            close: function() {

                ChangeUserPasswordModule.$modal.modal('hide');

            },

            triggerValidation: function() {
                ChangeUserPasswordModule.$form.validate().destroy();
                ChangeUserPasswordModule.$modal.trigger('run-validation');
            },

            applyFormSubmit: function() {

                ajaxForm('#change-user-password-form', {
                    handleToast: true,
                    showOverlayLoader: true,
                    success: function(res) {
                        ChangeUserPasswordModule.close();
                    }
                });
            }
        };
    </script>
@endpush
