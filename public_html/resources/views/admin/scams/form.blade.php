@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scam ??= null;
    $isUpdate = !!$scam;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="scam-form">
    @csrf
    @method($method)

    <div class="row">

        <div class="col-12">
            <x-admin.select2-ajax name='customer_id' label='Customer' id="customer_id" class="keep-disabled"
                placeholder='Search Customer' :route="route('admin.customers.select-search')" :default="$scam
                    ? ['id' => $scam->customer->id, 'label' => $scam->customer->fullNameWithFullPhoneNumber]
                    : null" paginate required :disabled="$isUpdate" />
        </div>

        <div class="col-lg-6">
            <x-admin.select name='scam_type_id' label='Scam Type' class="select2" required>
                <option value="" selected disabled>Select Scam Type</option>
                @foreach ($scamTypes as $scamType)
                    <option value="{{ $scamType->id }}" @selected($scam && $scam->scam_type_id == $scamType->id)>{{ $scamType->title }}</option>
                @endforeach
            </x-admin.select>
        </div>

        <div class="col-lg-6">
            <x-admin.input-group type='number' name='scam_amount' label='Scam Amount' placeholder='Enter scam amount'
                icon='ti ti-currency-rupee' :value="$scam?->scam_amount" />
        </div>


        <div class="col-12">
            <x-admin.select2-ajax name='scam_source_id' label='Scam Source' id="scam_source_id" placeholder='Search Scam Source' :route="route('admin.scam-sources.select-search')" :default="$scam?->scamSource ? ['id' => $scam?->scamSource->id, 'label' => $scam?->scamSource->title] : null" minimumInputLength="0" paginate />
        </div>

        <div class="col-12">
            <x-admin.textarea name='customer_description' label='Customer Description'
                placeholder='Enter customer description (max 1000 characters)' rows='4' :value="$scam?->customer_description" />
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
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\ScamRequest::class, '#scam-form') !!}

    <script>
        $(document).ready(function() {

            ajaxForm('#scam-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });

        });
    </script>
@endpush
