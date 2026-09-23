@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Admin\UpdateAccountSettingsRequest)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.account-settings.index'),
])

@section('content')
    <div class="card">
        <div class="row g-0">
            <div class="col-12 col-md-3 border-end">
                <div class="card-body">
                    <h4 class="subheader">Business settings</h4>
                    <div class="list-group list-group-transparent">
                        <a href="{{ route('admin.account-settings.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center active">
                            My Account
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-9 d-flex flex-column">
                <form id="account-settings-form" action="{{ route('admin.account-settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="delete_avatar" id="delete_avatar_input" value="0">
                    <div class="card-body">
                        <h2 class="mb-4">My Account</h2>
                        <h3 class="card-title">Profile Details</h3>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="{{ $user->profile_avatar }}" alt="{{ $user->username }}" id="profile_picture_preview"
                                    class="avatar avatar-xl avatar-rounded">
                            </div>
                            <div class="col-auto">
                                <x-admin.button type="button" label='Change avatar' variant='none'
                                    onclick="$('#profile_picture_input').click();" />
                            </div>
                            <div class="col-auto">
                                <x-admin.button type="button" label='Delete avatar' variant='ghost-danger' id="delete_avatar_btn" />
                            </div>
                            <input type='file' id="profile_picture_input" name='profile_picture' accept="image/*" style="display: none;" />
                        </div>

                        <h3 class="card-title mt-4">User Profile</h3>

                        <div class="row g-3">

                            <div class="col-lg-6">
                                <x-admin.input label='Business Name' name='name' placeholder='Enter your name'
                                    :value="$user->name" required />
                            </div>

                            <div class="col-lg-6">
                                <x-admin.input label='Username' name='username' placeholder='Enter your username'
                                    :value="$user->username" required />
                            </div>

                            <div class="col-lg-6">
                                <x-admin.country-select2 name='country_code' label='Select Country' id="user-country-select"
                                    :default="$user?->country_code ?? 'in'" required />
                            </div>

                            <div class="col-lg-6">
                                <x-admin.input-group name='phone_number' id="phone_number" type='number' label="Phone Number"
                                    class="phone-input" :value="$user?->phone_number" required />
                            </div>

                            <div class="col-12">
                                <x-admin.input label='Email Address' name='email' placeholder='Enter your email'
                                    :value="$user?->email" required />
                            </div>

                            <div class="col-12">
                                <x-admin.textarea label='Quote' name='quote' placeholder='Enter your motivational quote'
                                    :value="$user?->quote" />
                            </div>

                        </div>

                    </div>

                    <div class="card-footer bg-transparent mt-auto">

                        <div class="btn-list justify-content-end">

                            <x-admin.button label="Save Changes" submit />

                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    {!! JsValidatorFacade::formRequest(UpdateAccountSettingsRequest::class, '#account-settings-form') !!}

    <script>
        function uploadAndUpdateImage(imageInputSelector, imageFieldSelector) {
            $(imageInputSelector).on('change', function(event) {
                const file = this.files[0];
                const preview = $(imageFieldSelector);

                if (file) {
                    $('#delete_avatar_input').val('0');
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                }
            });
        }

        $(document).ready(function() {

            uploadAndUpdateImage('#profile_picture_input', '#profile_picture_preview');

            $('#delete_avatar_btn').on('click', function() {
                $('#delete_avatar_input').val('1');
                $('#profile_picture_input').val('');
                const name = $('input[name="name"]').val() || 'User';
                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0054a6&color=fff&size=128`;
                $('#profile_picture_preview').attr('src', avatarUrl);
            });

            $('#user-country-select').on('change', function() {
                const countryKey = $(this).val();
                const country = window.countries[countryKey];
                if (country) {
                    $('#phone_number').siblings('.input-icon-addon').html('+' + country.calling_code);
                }
            }).trigger('change');

            ajaxForm('#account-settings-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });
        });
    </script>
@endpush
