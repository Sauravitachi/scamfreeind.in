@use(Diglactic\Breadcrumbs\Breadcrumbs)

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
                        {{-- <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">My
                            Notifications</a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">Connected
                            Apps</a>
                        <a href="./settings-plan.html"
                            class="list-group-item list-group-item-action d-flex align-items-center">Plans</a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">Billing
                            &amp; Invoices</a> --}}
                    </div>
                    {{-- <h4 class="subheader mt-4">Experience</h4>
                    <div class="list-group list-group-transparent">
                        <a href="#" class="list-group-item list-group-item-action">Give Feedback</a>
                    </div> --}}
                </div>
            </div>
            <div class="col-12 col-md-9 d-flex flex-column">
                <div class="card-body">
                    <h2 class="mb-4">My Account</h2>
                    <h3 class="card-title">Profile Details</h3>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            {{-- <span class="avatar avatar-xl avatar-rounded"
                                style="background-image: url({{ $user->profile_avatar }})"></span> --}}
                            <img src="{{ $user->profile_avatar }}" alt="{{ $user->username }}" id="profile_picture_preview"
                                class="avatar avatar-xl avatar-rounded">
                        </div>
                        <div class="col-auto">
                            <x-admin.button label='Change avatar' variant='none'
                                onclick="$('#profile_picture_input').click();" />
                        </div>
                        <div class="col-auto">
                            <x-admin.button label='Delete avatar' variant='ghost-danger' />
                        </div>
                        <input type='file' id="profile_picture_input" name='profile_picture' style="display: none;" />
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

                    </div>

                </div>

                <div class="card-footer bg-transparent mt-auto">

                    <div class="btn-list justify-content-end">

                        <a href="#" class="btn btn-primary">
                            Submit
                        </a>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function uploadAndUpdateImage(imageInputSelector, imageFieldSelector) {
            $(imageInputSelector).on('change', function(event) {
                const file = this.files[0];
                const preview = $(imageFieldSelector);

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(file);
                } else {
                    preview.attr('src', '').hide();
                }
            });
        }

        $(document).ready(function() {

            uploadAndUpdateImage('#profile_picture_input', '#profile_picture_preview');

            $('#user-country-select').on('change', function() {
                const countryKey = $(this).val();
                const country = window.countries[countryKey];
                if (country) {
                    $('#phone_number').siblings('.input-icon-addon').html('+' + country.calling_code);
                }
            }).trigger('change');
        });
    </script>
@endpush
