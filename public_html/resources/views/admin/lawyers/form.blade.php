@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $lawyer ??= null;
    $isUpdate = !!$lawyer;
    $url = $isUpdate ? route('admin.lawyers.update', $lawyer) : route('admin.lawyers.store');
    $selectedSpecs = $lawyer ? $lawyer->specializations->pluck('id')->toArray() : [];
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $url }}" method="POST" id="lawyer-form" enctype="multipart/form-data">
    @csrf
    @if ($isUpdate)
        @method('PUT')
    @endif

    <div class="row mb-5">
        <div class="col-lg-6">
            <x-admin.input name='name' label='Lawyer Name' placeholder='Enter Lawyer Name' :value="$lawyer?->name" required />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='email' type='email' label='Email Address' placeholder='Enter Email Address' :value="$lawyer?->email" />
        </div>
        <div class="col-lg-6">
            <x-admin.input name='phone' label='Phone Number' placeholder='Enter Phone Number' :value="$lawyer?->phone" />
        </div>
        <div class="col-lg-6">
            <x-admin.select name='specializations[]' id="specializations-select" label='Specializations' class="select2 form-select" multiple>
                @foreach ($specializations as $spec)
                    <option value="{{ $spec->id }}" @selected(in_array($spec->id, $selectedSpecs))>{{ $spec->title }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <div class="col-lg-6">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar-upload">
                    <div class="avatar-preview">
                        <div class="avatar-preview-inner" id="imagePreview" style="background-image: url({{ $lawyer?->image ? asset('storage/' . $lawyer->image) : 'https://ui-avatars.com/api/?name=' . urlencode($lawyer?->name ?? '') . '&background=random&color=fff&bold=true' }});"></div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <x-admin.input type="file" name="image" id="imageInput" label="Lawyer Image" accept="image/*" />
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <x-admin.textarea name='address' label='Address' placeholder='Enter Address' :value="$lawyer?->address" />
        </div>
        

        
        <div class="col-12 mt-3 mb-3">
            <input type="hidden" name="is_active" value="0" />
            <x-admin.checkbox name='is_active' label='Is Active?' :checked="!$isUpdate || !!$lawyer?->is_active" />
        </div>

        <div class="col-12 mt-4">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>
    </div>
</form>

@push('style')
<style>
    .avatar-upload {
        position: relative;
        max-width: 100px;
    }
    .avatar-upload .avatar-preview {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 100%;
        border: 3px solid #f0f2f5;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .avatar-upload .avatar-preview-inner {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
</style>
@endpush

@push('script')
    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\LawyerRequest::class, '#lawyer-form') !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            // Initialize select2
            $('#specializations-select').select2({
                placeholder: 'Select Specializations',
                allowClear: true
            });

            // Update image preview on change
            $('#imageInput').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').css('background-image', `url(${e.target.result})`);
                    }
                    reader.readAsDataURL(file);
                }
            });

            ajaxForm('#lawyer-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });
        });
    </script>
@endpush
