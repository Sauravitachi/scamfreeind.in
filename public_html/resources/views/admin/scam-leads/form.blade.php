@php
    /** @var \App\Models\ScamLead $scamLead */
@endphp

@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scamLead ??= null;
    $isUpdate = !!$scamLead;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="scam-lead-form">
    @csrf
    @method($method)

    <div class="row">

        <div class="col-lg-6">
            <x-admin.input name='name' label="Customer Name" placeholder="Enter customer name" :value="$scamLead?->name" />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='email' label="Email Address" placeholder="Enter email address" :value="$scamLead?->email" />
        </div>

        <div class="col-lg-6">
            <x-admin.country-select2 name='country_code' label='Select Country' default="in"
                id="customer-country-select" :default="$scamLead?->country_code ?? 'in'" required />
        </div>

        <div class="col-lg-6">
            <x-admin.input-group name='phone_number' id="phone_number" type='number' label="Phone Number"
                class="phone-input" placeholder="Enter phone number" :value="$scamLead?->phone_number" required />
        </div>

        <div class="col-lg-4">
            <x-admin.select2-ajax name='scam_source_id' label='Scam Source' id="scam_source_id" placeholder='Search Scam Source' :route="route('admin.scam-sources.select-search')" :default="$scamLead?->scamSource ? ['id' => $scamLead?->scamSource->id, 'label' => $scamLead?->scamSource->title] : null" minimumInputLength="0" paginate />
        </div>

        <div class="col-lg-4">
            <x-admin.select name='scam_type_id' label='Scam Type' class="select2">
                <option value="" selected disabled>Select Scam Type</option>
                @foreach ($scamTypes as $scamType)
                    <option value="{{ $scamType->id }}" @selected($scamLead && $scamLead->scam_type_id == $scamType->id)>{{ $scamType->title }}</option>
                @endforeach
            </x-admin.select>
        </div>

        <div class="col-lg-4">
            <x-admin.input-group type='number' name='scam_amount' label='Scam Amount' placeholder='Enter scam amount'
                icon='ti ti-currency-rupee' :value="$scamLead?->scam_amount" />
        </div>

        <div class="col-12">
            <x-admin.textarea name='customer_description' label='Customer Description'
                placeholder='Enter customer description (max 1000 characters)' rows='4' :value="$scamLead?->customer_description" />
        </div>

    </div>

    <div class="text-end">
        @if ($isUpdate)
            <x-admin.button label="Save Changes" icon='ti ti-device-floppy' submit />
        @else
            <x-admin.button label="Create" icon='ti ti-plus' submit />
        @endif
    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\ScamLeadRequest::class, '#scam-lead-form') !!}

    <script>
        $(document).ready(function() {

            ajaxForm('#scam-lead-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });

            $('#customer-country-select').on('change', function() {
                const countryKey = $(this).val();
                const country = window.countries[countryKey];
                if (country) {
                    $('#phone_number').siblings('.input-icon-addon').html('+' + country.calling_code);
                }
            }).trigger('change');
        });
    </script>
@endpush
