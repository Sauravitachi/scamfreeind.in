@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Admin\UserRequest)

@php
    $user ??= null;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="user-form">
    @csrf
    @method($method)
    <div class="row">

        <div class="col-lg-6">
            <x-admin.input name='name' label="Name" placeholder="Enter name" :value="$user?->name" required />
        </div>

        <div class="col-lg-6">
            <x-admin.input name='username' label="Username" placeholder="Enter username" :value="$user?->username" required />
        </div>

        <div class="col-12">
            <x-admin.input name='email' label="Email Address" placeholder="Enter email address" :value="$user?->email"
                required />
        </div>

        {{-- <div class="col-lg-6">
            <x-admin.country-select2 name='country_code' label='Select Country' id="user-country-select"
                :default="$user?->country_code ?? 'in'" required />
        </div> --}}

        <div class="col-lg-6">
            <x-admin.input-group name='phone_number' id="phone_number" type='number' label="Phone Number"
                class="phone-input" :value="$user?->phone_number" required />
        </div>

        @if (!$user)
            <div class="col-lg-6">
                <x-admin.input name='password' type="password" label="Password" placeholder="Create password"
                    required />
            </div>

            <div class="col-lg-6">
                <x-admin.input name='confirm_password' type="password" label="Confirm Password"
                    placeholder="Confirm password" required />
            </div>
        @endif

        <div class="col-12">
            <x-admin.select name="role" label="Assign Role" class="select2">
                <option value="" selected disabled>Select Role</option>
                @php($userRole = $user?->roles->first() ?? null)
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}"
                        @if ($user) @selected($userRole->id == $role->id) @endif>{{ $role->name }}
                    </option>
                @endforeach
            </x-admin.select>
        </div>

    </div>

    <div class="text-end">
        <x-admin.button label="{{ $user ? 'Update' : 'Create' }}" icon='ti ti-plus' submit />
    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(UserRequest::class, '#user-form') !!}

    <script>
        $(document).ready(function() {

            ajaxForm('#user-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });

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
