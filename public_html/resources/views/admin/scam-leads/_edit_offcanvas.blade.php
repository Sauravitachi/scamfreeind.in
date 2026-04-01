@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Admin\ScamLeadRequest)
@use(App\Utilities\Html)

@php
    $lead = \App\Models\ScamLead::first();
@endphp

<div class="offcanvas offcanvas-mid offcanvas-end" tabindex="-1" id="scam-lead-edit-offcanvas">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title">Edit Lead</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="scam-lead-form" method="POST">
            @method('PUT')
            @csrf
            <input type="hidden" name="toast" value="1" />
            <div class="row">
    
                <div class="col-lg-6">
                    <x-admin.input name='name' label="Customer Name" placeholder="Enter customer name" />
                </div>
        
                <div class="col-lg-6">
                    <x-admin.input name='email' label="Email Address" placeholder="Enter email address" />
                </div>
        
                <div class="col-lg-6">
                    <x-admin.select name='country_code' label='Select Country' id="customer-country-select" required />
                </div>
        
                <div class="col-lg-6">
                    <x-admin.input-group name='phone_number' id="phone_number" type='number' label="Phone Number"
                        class="phone-input" placeholder="Enter phone number" required />
                </div>
        
                <div class="col-lg-4">
                    <x-admin.select name='scam_source_id' label='Scam Source' id="scam_source_id" placeholder='Scam Source' :options="$scamSources->pluck('title', 'id')->toArray()" />
                </div>
        
                <div class="col-lg-4">
                    <x-admin.select name='scam_type_id' id="scam_type_id" label='Scam Type'>
                        <option value="" selected disabled>Select Scam Type</option>
                        @foreach ($scamTypes as $scamType)
                            <option value="{{ $scamType->id }}">{{ $scamType->title }}</option>
                        @endforeach
                    </x-admin.select>
                </div>
        
                <div class="col-lg-4">
                    <x-admin.input-group type='number' name='scam_amount' label='Scam Amount' placeholder='Enter scam amount'
                        icon='ti ti-currency-rupee' />
                </div>
        
                <div class="col-12">
                    <x-admin.textarea name='customer_description' label='Customer Description'
                        placeholder='Enter customer description (max 1000 characters)' rows='4' />
                </div>
            </div>
            <div class="text-end">
                <x-admin.button label="Save Changes" icon='ti ti-device-floppy' submit />
            </div>
        </form>
    </div>
</div>


@push('script')

    @php
        $selectCountriesData = Html::selectCountriesData();
    @endphp

    {!! JsValidatorFacade::formRequest(ScamLeadRequest::class, '#scam-lead-form') !!}

    <script>
        
        const EDIT_LEAD_MODULE = {

            scamLeadId: null,

            $offcanvas: null,
            $offcanvasBody: null,

            register: function() {
                
                EDIT_LEAD_MODULE.$offcanvas = $('#scam-lead-edit-offcanvas');
                EDIT_LEAD_MODULE.$offcanvasBody = EDIT_LEAD_MODULE.$offcanvas.find('.offcanvas-body');

                EDIT_LEAD_MODULE.$offcanvas.on('hide.bs.offcanvas', function () {
                    replaceUrl("{{ route('admin.scam-leads.index') }}");
                });

            },

            prepareUi: function() {

                if(!EDIT_LEAD_MODULE.scamLeadId) {
                    return;
                }

                // making ajax request
                const url = '{{ route('admin.scam-leads.show', ':id') }}'.replace(':id', EDIT_LEAD_MODULE.scamLeadId);


                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: function(){
                        overlayLoader.show();
                    },
                    success: function(res) {
                        if(res.success) {

                            const scamLead = res.data;

                            const $form = EDIT_LEAD_MODULE.$offcanvasBody.find('form');

                            const formActionUrl = "{{ route('admin.scam-leads.update', ':id') }}".replace(':id', scamLead.id);

                            $form.attr('action', formActionUrl);

                            $form.find('input[name="name"]').val(scamLead.name);
                            $form.find('input[name="email"]').val(scamLead.email);
                            $form.find('input[name="phone_number"]').val(scamLead.phone_number);
                            $form.find('select[name="country_code"]').val(scamLead.country_code);
                            $form.find('select[name="scam_source_id"]').val(scamLead.scam_source_id);
                            $form.find('select[name="scam_type_id"]').val(scamLead.scam_type_id);
                            $form.find('input[name="scam_amount"]').val(scamLead.scam_amount);
                            $form.find('input[name="customer_description"]').val(scamLead.customer_description);


                            $('#customer-country-select').trigger('change');

                            initSelect2($('#customer-country-select'), { dropdownParent: EDIT_LEAD_MODULE.$offcanvasBody });
                            initSelect2($('#scam_source_id'), { dropdownParent: EDIT_LEAD_MODULE.$offcanvasBody });
                            initSelect2($('#scam_type_id'), { dropdownParent: EDIT_LEAD_MODULE.$offcanvasBody });

                            $form.valid();

                            EDIT_LEAD_MODULE.$offcanvas.offcanvas('show');

                            const editUrl = "{{ route('admin.scam-leads.edit', ':id') }}".replace(':id', scamLead.id);
                            replaceUrl(editUrl);

                            return;
                        }

                        toast.open({type: 'error', message: 'Something went wrong!'});
                    },
                    complete: function() {
                        overlayLoader.hide();
                    }
                });


                $('#customer-country-select').on('change', function() {
                    const countryKey = $(this).val();
                    const country = window.countries[countryKey];
                    if (country) {
                        $('#phone_number').siblings('.input-icon-addon').html('+' + country.calling_code);
                    }
                });
            },

            open: function(scamLeadId) {

                EDIT_LEAD_MODULE.scamLeadId = scamLeadId;

                // EDIT_LEAD_MODULE.$offcanvasBody.html(`<div class="h2 h-100 d-flex justify-content-center align-items-center">${Loader.spinner}</div>`);

                EDIT_LEAD_MODULE.prepareUi();
            },

            close: function() {
                EDIT_LEAD_MODULE.$offcanvas.offcanvas('hide');
            }
        };

        $(document).ready(function() {

            EDIT_LEAD_MODULE.register();



            window.countries = {!! $selectCountriesData !!};
            const select = $('#customer-country-select');
            select.empty();
            select.append(`<option value="" selected disabled>Select Country</option>`);
            $.each(window.countries, function(key, country) {
                select.append(`<option value="${key}">${country.emoji} ${country.name}</option>`);
            });



            ajaxForm('#scam-lead-form', {
                handleToast: true,
                success: function(res) {
                    if(res.success) {
                        EDIT_LEAD_MODULE.close();
                        dtTable.draw(false);
                    }
                }
            });

        });

    </script>
@endpush
