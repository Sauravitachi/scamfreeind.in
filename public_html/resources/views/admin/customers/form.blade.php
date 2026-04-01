@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $customer = $customer ?? null;
    $isUpdate = !!$customer;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="customer-form">
    @csrf
    @method($method)

    <div class="row">

        <div class="col-lg-6">
            <x-admin.input name='first_name' label="First Name" placeholder="Enter first name" :value="$customer?->first_name"
                required />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='last_name' label="Last Name" placeholder="Enter last name" :value="$customer?->last_name" />
        </div>

        <div class="col-lg-5">
            <x-admin.input name='email' label="Email Address" placeholder="Enter email address" :value="$customer?->email" />
        </div>

        {{-- <div class="col-lg-3">
            <x-admin.country-select2 name='country_code' label='Select Country' id="customer-country-select"
                :default="$customer?->country_code ?? 'in'" required />
        </div> --}}

        <div class="col-lg-4">
            <x-admin.input-group name='phone_number' id="phone_number" type='number' label="Phone Number"
                class="phone-input" placeholder="Enter phone number" :value="$customer?->phone_number" required />
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
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\CustomerRequest::class, '#customer-form') !!}

    <script>
        $(document).ready(function() {

            ajaxForm('#customer-form', {
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
