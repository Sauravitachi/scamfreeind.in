

@php
    $assetsVersion = config('settings.assets_version');
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="_token" content="{{ csrf_token() }}" id="csrf">
    <title>{{ $pageTitle ?? 'Dashboard' }} | Dashboard | {{ config('settings.brand_name') }}</title>
    <!-- CSS files -->
    <link href="{{ asset('assets/theme/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/theme/css/tabler-flags.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/theme/css/tabler-payments.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/theme/css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-notify/dist/simple-notify.css" />
    <link rel="stylesheet" href="{{ asset('assets/theme/style.css') . '?v=' . $assetsVersion }}">
    <link rel="stylesheet" href="{{ asset('assets/theme/loaders.css') . '?v=' . $assetsVersion }}">
{{-- 
    {{-- <link rel="icon" href="{{ asset('assets/theme/img/favicon.ico') . '?v=' . $assetsVersion }}" --}}


    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .page-wrapper .page-header {
            margin-top: 10px;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        .fit-content {
            width: fit-content !important;
        }

        table th,
        table td {
            text-align: start !important;
        }

        .dropzone {
            border: var(--tblr-border-width) dashed var(--tblr-border-color) !important;
        }

        .resize-none {
            resize: none;
        }
    </style>

    @stack('style')

    <x-admin.microsoft-clarity />

</head>


<body class="layout-fluid modern-bg">

    <script src="{{ asset('assets/theme/js/demo-theme.min.js') }}"></script>

    <input type="text" style="display:none">

    @include('customer.layouts.components.overlay-loader')

    <div class="page">
        @include('customer.layouts.components.header')
        <div class="page-wrapper">
            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">
                    <div class="modern-card">
                        @yield('content')
                    </div>
                </div>
            </div>
            @include('customer.layouts.components.footer')
            <form style="display: hidden;" action="{{ route('customer.logout') }}" method="POST" id="logoutPost">
                @csrf
            </form>
        </div>
    </div>

    <script type="text/javascript" src="{{ asset('assets/theme/js/tabler.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-notify/dist/simple-notify.min.js"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/notyf/notyf.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/js/script.js') . "?v={$assetsVersion}" }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/script.js') . "?v={$assetsVersion}" }}"></script>

    @stack('script')

    @include('customer.layouts.components.flash')

    <script>
        function pageButtonVisibility(selector, show) {
            const target = $(selector).closest('div.page-btn-parent');
            show ? target.show() : target.hide();
        }

        $(document).ready(function() {
            const loginPageUrl = @js(route('customer.login'));
            if (document.referrer && document.referrer !== loginPageUrl) {
                $('#backButton').show();
            }
            $('#backButton').on('click', function() {
                window.history.back();
            });
            window.addEventListener('popstate', function(event) {
                if (document.referrer && document.referrer !== loginPageUrl) {
                    $('#backButton').show();
                } else {
                    $('#backButton').hide();
                }
            });
        });
    </script>
</body>

</html>
