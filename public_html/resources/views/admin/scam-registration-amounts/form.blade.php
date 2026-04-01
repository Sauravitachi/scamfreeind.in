@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scamRegistrationAmount ??= null;
    $isUpdate = !!$scamRegistrationAmount;
@endphp

<form action="{{ $actionUrl }}" method="POST" id="scam-registration-amount-form">
    @csrf
    @method($method)
    <div class="row mb-3">
        <div class="col-lg-4">
            <x-admin.input name='title' label='Title' placeholder='Enter Title'
                :value="$scamRegistrationAmount?->title" required />
        </div>
        <div class="col-lg-4">
            <x-admin.input type='number' min="0" step="0.01" name='amount' label='Amount' placeholder='Enter Amount'
                :value="$scamRegistrationAmount?->amount" required />
        </div>
        <div class="col-lg-4">
            <x-admin.input type='number' min="0" step="0.01" name='points' label='Points' placeholder='Enter points'
                :value="$scamRegistrationAmount?->points" />
        </div>
        <div class="col-12">
            <x-admin.textarea name='description' label='Description' placeholder='Enter description (max 1000 characters)' :value="$scamRegistrationAmount?->description" />
        </div>
        <div class="col-lg-4 col-md-6">
            <input type="hidden" name="is_active" value="0" />
            <x-admin.checkbox name='is_active' label='Is Active?' value="1" :checked="!!$scamRegistrationAmount?->is_active" />
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
        \App\Http\Requests\Admin\ScamRegistrationAmountRequest::class,
        '#scam-registration-amount-form',
    ) !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#scam-registration-amount-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });
        });
    </script>
  
@endpush
