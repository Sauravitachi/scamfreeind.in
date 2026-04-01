@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php $assetsVersion = time() @endphp

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    
    <meta name="_token" content="{{ csrf_token() }}">
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

    

    <x-admin.microsoft-clarity />
</head>



<body class=" d-flex flex-column">
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

                @error('login_failed')
                    <x-admin.alert variant='danger' icon='ti ti-exclamation-circle' :message='$message' />
                @enderror

                <form action="{{ route('admin.auth.handle-login') }}" method="POST" id="login-form" autocomplete="off"
                    novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email/Username</label>
                        <input type="text" name="identifier" class="form-control"
                            placeholder="Enter email or username" autocomplete="off" />
                    </div>
                    <div class="mb-2">
                        <label class="form-label">
                            Password
                        </label>
                        <input name="password" type="password" class="form-control is-invalid"
                            placeholder="Your password" autocomplete="off">
                    </div>
                    <div class="mt-4">
                        <label class="form-check">
                            <input name="remember" type="checkbox" class="form-check-input" />
                            <span class="form-check-label">Remember me on this device</span>
                        </label>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Sign in
                        </button>
                    </div>
                </form>

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
                style="background-image: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-1.2.1-1&auto=format&fit=crop&w=1950&q=80')">
            </div>
        </div>
    </div>
    
    <!-- Libs JS -->
    <script type="text/javascript" src="{{ asset('assets/common/plugins/jquery/jquery.min.js') . "?v={$assetsVersion}"  }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script type="text/javascript" src="{{ asset('public/vendor/jsvalidation/js/jsvalidation.js') . "?v={$assetsVersion}"  }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/notyf/notyf.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/js/script.js') . "?v={$assetsVersion}"  }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/script.js') . "?v={$assetsVersion}" }}"></script>
    <script src="{{ asset('assets/theme/js/tabler.min.js') . "?v={$assetsVersion}"  }}"></script>

    @include('admin.layouts.components.sounds')

    {!! JsValidatorFacade::formRequest(\App\Http\Requests\Admin\Auth\LoginRequest::class, '#login-form') !!}

    <script>



        $(document).ready(function() {
            ajaxForm('#login-form', {
                responseRedirect: true,
                beforeSend: function() {
                    FFSound.click();
                },
                error: function(xhr) {
                    showValidationErrors(xhr);
                }
            });
        });
    </script>

</body>

</html>
