<div class="offcanvas offcanvas-full offcanvas-end" tabindex="-1" id="user-detail-offcanvas">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title">User Details</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    </div>
</div>

@push('script')
    <script>
        const UserDetailModule = {
            userId: null,
            $offcanvase: null,
            $offcanvasBody: null,

            showUrl: @js(route('admin.users.show', ':id')),

            register: function() {

            },

            prepare: function() {

                UserDetailModule.$offcanvas = $('#user-detail-offcanvas');
                UserDetailModule.$offcanvasBody = UserDetailModule.$offcanvas.find('.offcanvas-body');

            },

            open: function(userId) {

                if (!userId)
                    return;

                UserDetailModule.userId = userId;
                UserDetailModule.prepare();
                UserDetailModule.$offcanvas.offcanvas('show');

                UserDetailModule.refresh();

            },


            refresh: function() {

                if (!UserDetailModule.userId) {
                    return;
                }

                const url = UserDetailModule.showUrl.replace(':id', UserDetailModule.userId);

                runAjax({
                    url,
                    method: 'GET',
                    beforeSend: function() {
                        UserDetailModule.$offcanvasBody.html(Loader.centerSpinnerLoader(
                            'Loading details'));
                    },
                    success: function(res) {
                        if (res.html) {

                            UserDetailModule.$offcanvasBody.html(res.html);

                            $(".timeline_container").animate({
                                scrollTop: 1e10
                            }, 1000);

                        }
                    }
                });

            },

        };



        $(document).ready(function() {
            UserDetailModule.register();
        });
    </script>
@endpush
