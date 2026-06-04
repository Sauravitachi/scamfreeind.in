@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $specialization ??= null;
    $isUpdate = !!$specialization;

    $url = $isUpdate ? route('admin.specializations.update', $specialization) : route('admin.specializations.store');
@endphp

<form action="{{ $url }}" method="POST" id="specialization-form">
    @csrf
    @if ($isUpdate)
        @method('PUT')
    @endif
    <div class="row mb-5">

        <div class="col-lg-6">
            <x-admin.input name='slug' label='Specialization Slug' placeholder='Enter Specialization slug' :value="$specialization?->slug"
                required />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='title' label='Specialization Title' placeholder='Enter Specialization Title' :value="$specialization?->title"
                required />
        </div>


        <div class="col-lg-4 col-md-6 mt-3">
            <input type="hidden" name="is_default" value="0" />
            <x-admin.checkbox name='is_default' label='Is Default?' :checked="!!$specialization?->is_default" />
        </div>


        <div class="col-12 mt-4">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>


    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\SpecializationRequest::class, '#specialization-form') !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#specialization-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });
        });
    </script>
@endpush
