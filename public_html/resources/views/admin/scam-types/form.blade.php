@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scamType ??= null;
    $isUpdate = !!$scamType;

    $url = $isUpdate ? route('admin.scam-types.update', $scamType) : route('admin.scam-types.store');
@endphp

<form action="{{ $url }}" method="POST" id="scam-type-form">
    @csrf
    @if ($isUpdate)
        @method('PUT')
    @endif
    <div class="row mb-5">

        <div class="col-lg-6">
            <x-admin.input name='slug' label='Scam Type Slug' placeholder='Enter Scam Type slug' :value="$scamType?->slug"
                required />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='title' label='Scam Type Title' placeholder='Enter Scam Type Title' :value="$scamType?->title"
                required />
        </div>


        <div class="col-lg-4 col-md-6">
            <input type="hidden" name="is_default" value="0" />
            <x-admin.checkbox name='is_default' label='Is Default?' :checked="!!$scamType?->is_default" />
        </div>


        <div class="col-12">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>


    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\ScamTypeRequest::class, '#scam-type-form') !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#scam-type-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });
        });
    </script>
@endpush
