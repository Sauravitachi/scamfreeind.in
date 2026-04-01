@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scamSource ??= null;
    $isUpdate = !!$scamSource;
@endphp

@include('admin.layouts.components.select2')
@include('admin.layouts.components.color-picker')

<form action="{{ $actionUrl }}" method="POST" id="scam-source-form">
    @csrf
    @method($method)
    <div class="row mb-3">
        <div class="col-lg-6">
            <x-admin.input name='slug' label='Scam Source Slug' placeholder='Enter Scam Source Slug' :value="$scamSource?->slug"
                required />
        </div>
        <div class="col-lg-6">
            <x-admin.input name='title' label='Scam Source Title' placeholder='Enter Scam Source Title'
                :value="$scamSource?->title" required />
        </div>
        <div class="col-lg-6">
            <x-admin.color-picker label='Indicator color' name='indicator_color' id="scam-color" :default="$scamSource?->indicator_color" required />
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
        \App\Http\Requests\Admin\ScamSourceRequest::class,
        '#scam-source-form',
    ) !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#scam-source-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });
        });
    </script>
  
@endpush
