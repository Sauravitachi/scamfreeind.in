@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Admin\ScamLeadRequest)
@use(App\Utilities\Html)

@php
    $lead = \App\Models\ScamLead::first();
@endphp

<div class="offcanvas offcanvas-full offcanvas-end" tabindex="-1" id="duplicate-lead-offcanvas">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title">Duplicate Leads</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
  
    </div>
</div>


@push('script')
    <script>


        const DUPLICATE_LEADS_MODULE = {

            scamLeadId: null,

            $offcanvas: null,
            $offcanvasBody: null,

            register: function() {
                
                DUPLICATE_LEADS_MODULE.$offcanvas = $('#duplicate-lead-offcanvas');
                DUPLICATE_LEADS_MODULE.$offcanvasBody = DUPLICATE_LEADS_MODULE.$offcanvas.find('.offcanvas-body');

            },

            open: function(scamLeadId) {

                DUPLICATE_LEADS_MODULE.scamLeadId = scamLeadId;

                DUPLICATE_LEADS_MODULE.refresh();

                
            },

            refresh: function() {
                $.ajax({
                    url: "{{ route('admin.scam-leads.similar-leads', ':id') }}".replace(':id', DUPLICATE_LEADS_MODULE.scamLeadId),
                    method: 'GET',
                    beforeSend: function() {
                        overlayLoader.show();
                    },
                    success: function(res) {

                        if(res.data.count <= 0) {
                            DUPLICATE_LEADS_MODULE.close();
                            return;
                        }

                        DUPLICATE_LEADS_MODULE.$offcanvasBody.html(res.html);
                        DUPLICATE_LEADS_MODULE.$offcanvas.offcanvas('show');
                    },
                    complete: function() {
                        overlayLoader.hide();
                    }
                });
            },

            close: function() {
                DUPLICATE_LEADS_MODULE.$offcanvas.offcanvas('hide');
            }
            
        };

        $(document).ready(function() {
            DUPLICATE_LEADS_MODULE.register();
        });


    </script>
@endpush
