@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

<form action="{{ route('admin.permissions.update', $permission) }}" method="POST" id="permission-form">
    @csrf
    @method('PUT')
    <div class="row mb-5">
        <div class="col-md-6">
            <x-admin.input name='name' label='Permission Name' :value="$permission->name" disabled />
        </div>
        <div class="col-md-6">
            <x-admin.input name='label' label='Permission Label' placeholder='Enter Permission Label' :value="$permission->label"
                required />
        </div>
        <div class="col-12">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>
    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\PermissionRequest::class, '#permission-form') !!}
    <script>
        ajaxForm('#permission-form', {
            handleToast: true
        });
    </script>
@endpush
