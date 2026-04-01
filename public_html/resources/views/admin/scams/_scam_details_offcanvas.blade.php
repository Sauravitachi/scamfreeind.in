@push('style')
    <style>
        .scam-file-preview {
            height: 130px;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
        }
    </style>
@endpush

<div class="offcanvas offcanvas-full offcanvas-end" tabindex="-1" id="scam-detail-offcanvas">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title">Scam Details</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    </div>
</div>

@push('script')
    <script>
        const ScamDetailModule = {
            scamId: null,
            $offcanvase: null,
            $offcanvasBody: null,

            scamUrl: @js(route('admin.scams.show', ':id')),
            deleteScamStatusFileUrl: @js(route('admin.scams.delete-scam-status-file', ':key')),
            deleteScamFileUrl: @js(route('admin.scams.delete-scam-file', ':key')),

            register: function() {
                // ScamDetailModule.open(16);
            },

            prepare: function() {

                ScamDetailModule.$offcanvas = $('#scam-detail-offcanvas');
                ScamDetailModule.$offcanvasBody = ScamDetailModule.$offcanvas.find('.offcanvas-body');

            },

            open: function(scamId) {

                if (!scamId)
                    return;

                ScamDetailModule.scamId = scamId;
                ScamDetailModule.prepare();
                ScamDetailModule.$offcanvas.offcanvas('show');

                ScamDetailModule.refresh();

            },

            refresh: function() {

                if (!ScamDetailModule.scamId) {
                    return;
                }

                const url = ScamDetailModule.scamUrl.replace(':id', ScamDetailModule.scamId);

                const navValue = $('.scam-status-file-nav.active').data('value') ?? null;

                runAjax({
                    url,
                    method: 'GET',
                    beforeSend: function() {
                        ScamDetailModule.$offcanvasBody.html(Loader.centerSpinnerLoader(
                            'Loading details'));
                    },
                    success: function(res) {
                        if (res.html) {

                            ScamDetailModule.$offcanvasBody.html(res.html);

                            $(".timeline_container").animate({
                                scrollTop: 1e10
                            }, 1000);

                            ScamDetailModule.postRefreshProcessing();

                            if(navValue) {
                                $('.scam-status-file-nav').removeClass('active');
                                $('.scam-status-file-tab-pane').removeClass('active show');
                                $(`.scam-status-file-nav[data-value="${navValue}"]`).addClass('active');
                                $(`.scam-status-file-tab-pane[data-value="${navValue}"]`).addClass('active show');
                            }
                        }
                    }
                });

            },

            postRefreshProcessing: function() {

                $('.scam-files-card').each(function() {
                    $(this).find('a.card-link').each(function() {
                        if ($.trim($(this).text()) === '') {
                            $(this).remove();
                        }
                    });
                });

            },

            scamFileDownload: function(event) {
                const $imageElem = $(event.target).closest('.scam-file-preview');
                const filename = $imageElem.parent().data('file-name') ?? '';
                const url = $imageElem.parent().data('file-url') ?? '';
                downloadFileFromUrl(url, {
                    outputFileName: filename
                });
            },

            scamFileDelete: function (event) {

                const $elem = $(event.target).closest('.scam-file-preview').parent();
                const id = $elem.data('id');
                const type = $elem.data('type');
                
                if(id) {

                    const url = ScamDetailModule[type === 'scam_file' ? 'deleteScamFileUrl' : 'deleteScamStatusFileUrl'].replace(':key', id);

                    runAjax({
                        url,
                        method: 'DELETE',
                        handleToast: true,
                        success: function(res) {
                            if(res.success) {
                                ScamDetailModule.refresh();
                            }
                        }
                    });

                }
            }

        };



        $(document).ready(function() {
            ScamDetailModule.register();
        });
    </script>
@endpush
