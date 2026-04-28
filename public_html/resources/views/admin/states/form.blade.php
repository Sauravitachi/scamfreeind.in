@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $state ??= null;
    $isUpdate = !!$state;
@endphp

<form action="{{ $actionUrl }}" method="POST" id="state-form">
    @csrf
    @method($method)
    <div class="row mb-3">
        <div class="col-lg-6">
            <x-admin.input name='name' label='State Name' placeholder='Enter State Name' :value="$state?->name"
                required />
        </div>
        <div class="col-lg-6">
            <x-admin.input name='code' label='State Code' placeholder='Enter State Code'
                :value="$state?->code" />
        </div>
        <div class="col-lg-12 mt-3">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ ($state ? $state->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Is Active</label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
            <div class="text-end">
                <x-admin.button :label="$isUpdate ? 'Update State' : 'Create State'" submit />
            </div>
        </div>
    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(
        \App\Http\Requests\Admin\StateRequest::class,
        '#state-form',
    ) !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#state-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });
        });
    </script>
@endpush
