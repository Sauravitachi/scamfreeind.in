@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Enums\CustomerEnquiryStatusType)

@php /** @var \App\Models\CustomerEnquiryStatus $customerEnquiryStatus */ @endphp

@php
    $customerEnquiryStatus ??= null;
    $isUpdate = !!$customerEnquiryStatus;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="customer-enquiry-status-form">
    @csrf
    @method($method)
    <div class="row mb-3">
        <div class="col-lg-4">
            <x-admin.input name='slug' label='Customer Enquiry Status Slug' placeholder='Enter Customer Enquiry Status Slug' :value="$customerEnquiryStatus?->slug"
                required />
        </div>
        <div class="col-lg-4">
            <x-admin.input name='title' label='Customer Enquiry Status Title' placeholder='Enter Customer Enquiry Status Title'
                :value="$customerEnquiryStatus?->title" required />
        </div>
        <div class="col-lg-4">
            <x-admin.select class="select2" name='type' label='Type' :options="CustomerEnquiryStatusType::selectArray()" :selected="$customerEnquiryStatus?->type->value" placeholder='Select' required />
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-lg-3">
            <input type="hidden" name="is_remark_required" value="0" />
            <x-admin.checkbox name='is_remark_required' label='Is Remark Required?' value='1' :checked="!!$customerEnquiryStatus?->is_remark_required" />
        </div>
        <div class="col-lg-3">
            <input type="hidden" name="consider_resolved" value="0" />
            <x-admin.checkbox name='consider_resolved' label='Consider Resolved?' value='1' :checked="!!$customerEnquiryStatus?->consider_resolved" />
        </div>
    </div>

    <hr />

    <div>
        <div>
            <input type="hidden" name="unassign_scam" value="0" />
            <x-admin.checkbox name='unassign_scam' id="unassign-scam-chk" label='Unassign Case?' value='1' :checked="!!$customerEnquiryStatus?->unassign_scam" />
        </div>
        <div id="unassign-scam-options-container" class="row mt-4" @if(!$customerEnquiryStatus?->unassign_scam) style="display: none;" @endif>
            <div class="col-lg-4 col-xl-3 col-xxl-2">
                <x-admin.input type="number" step="1" min="1" name='unassign_scam_in_days' placeholder='Enter days'
                :value="$customerEnquiryStatus?->unassign_scam_in_days">
                    <x-slot:label>
                        Unassign in days
                    </x-slot:label>
                </x-admin.input>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>
    </div>

</form>

@push('script')
    {!! JsValidatorFacade::formRequest(
        \App\Http\Requests\Admin\CustomerEnquiryStatusRequest::class,
        '#customer-enquiry-status-form',
    ) !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#customer-enquiry-status-form', {
                handleToast: true,
                responseRedirect: true
            });


            $('#unassign-scam-chk').on('change', function() {
                $('#unassign-scam-options-container').toggle();
            });
        });
    </script>
@endpush
