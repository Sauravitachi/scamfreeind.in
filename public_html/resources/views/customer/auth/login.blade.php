@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Customer\Auth\SendOtpRequest)
@use(App\Http\Requests\Customer\Auth\ConfirmOtpRequest)

@php $assetsVersion = config('settings.assets_version') @endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>
        Log In | {{ config('settings.brand_name') }}
    </title>
    <!-- CSS files -->
    <link href="{{ asset('assets/theme/css/tabler.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="{{ asset('assets/theme/style.css') . '?v=' . $assetsVersion }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    
    <link rel="icon" href="{{ asset('assets/theme/img/favicon.ico') . '?v=' . $assetsVersion }}" type="image/x-icon">
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>
<body class="d-flex flex-column">
    <script src="{{ asset('assets/theme/js/demo-theme.min.js') }}"></script>
    <div class="row g-0 flex-fill">
        <div class="col-12 col-lg-6 col-xl-4 d-flex flex-column justify-content-center">
            <div class="container container-tight my-5 px-lg-5 flex-grow-1 d-flex flex-column justify-content-center">
                <div class="text-center mb-4">
                    <strong class="h1">
                        {{ config('settings.brand_name') }}
                    </strong>
                </div>

                @include('admin.layouts.components.app-mode-alert')
                
                <h2 class="h3 text-center mb-3">
                    Login to your account
                </h2>

                <div id="main-form-container">

                </div>

                <div id="phone-number-form-container" style="display: none;">
                    <form action="{{ route('customer.send-otp') }}" class="send-otp-form" method="POST" autocomplete="off" novalidate>
                        @csrf
                        <div class="mb-3">
                            <x-admin.input-group type='number' name='phone_number' label='Phone Number' iconText='91' />
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100">
                                Send OTP
                            </button>
                        </div>
                    </form>
                </div>

                <div id="otp-form-container" style="display: none;">
                    <form action="{{ route('customer.confirm-otp') }}" class="confirm-otp-form" method="POST" autocomplete="off" novalidate>
                        @csrf
                        <x-admin.alert icon='ti ti-circle-check' message="OTP has been sent to __phone_number__" variant='success' important />
                        <div class="mb-3">
                            <x-admin.input type='number' name='otp' label='Enter OTP' />
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100">
                                Confirm OTP
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center justify-content-center align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    Copyright &copy; {{ date('Y') }}
                                    <a href="https://adigitalblogger.com" class="link-secondary" target="_blank">
                                        {{ config('settings.brand_name') }}
                                    </a>.
                                    All rights reserved.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
            
        </div>
        <div class="col-12 col-lg-6 col-xl-8 d-none d-lg-block">
            <!-- Photo -->
            <div class="bg-cover h-100 min-vh-100"
                style="background-image: url({{ asset('static/photos/login-bg.jpeg') }})">
            </div>
        </div>
    </div>
    
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="{{ asset('assets/theme/js/tabler.min.js') . "?v={$assetsVersion}"  }}" defer></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/jquery/jquery.min.js') . "?v={$assetsVersion}"  }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') . "?v={$assetsVersion}"  }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/notyf/notyf.min.js') }}"></script>
    
    <script type="text/javascript" src="{{ asset('assets/common/js/script.js') . "?v={$assetsVersion}"  }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/script.js') . "?v={$assetsVersion}" }}"></script>

    {!! js_validation_custom_event(SendOtpRequest::class, 'form.send-otp-form', '#main-form-container', 'validate-send-otp') !!}

    {!! js_validation_custom_event(ConfirmOtpRequest::class, 'form.confirm-otp-form', '#main-form-container', 'validate-confirm-otp') !!}
    
    <script>
        const LOGIN_MODULE = {
            register: function() {
                LOGIN_MODULE.$mainFormContainer = $('#main-form-container');
                LOGIN_MODULE.phoneNumberFormTemplate = $('#phone-number-form-container').html();
                LOGIN_MODULE.otpFormTemplate = $('#otp-form-container').html();

                LOGIN_MODULE.enablePhoneNumberForm();
            },
            enablePhoneNumberForm: function() {
                LOGIN_MODULE.$mainFormContainer.html(LOGIN_MODULE.phoneNumberFormTemplate);
                $('#main-form-container').trigger('validate-send-otp');
                ajaxForm('form.send-otp-form', {
                    handleToast: true,
                    success: function(res) {
                        if(res.success) {
                            LOGIN_MODULE.enableOtpForm(res.data.request);
                        }
                    }
                });
            },
            enableOtpForm: function(request) {
                const template = LOGIN_MODULE.otpFormTemplate
                    .replace(/__phone_number__/g, request.phone_number);
                LOGIN_MODULE.$mainFormContainer.html(template);
                $('#main-form-container').trigger('validate-confirm-otp');
                ajaxForm('form.confirm-otp-form', {
                    responseRedirect: true
                });
            }
        };


        $(document).ready(function() {
            LOGIN_MODULE.register();
        });
    </script>
</body>
</html>
