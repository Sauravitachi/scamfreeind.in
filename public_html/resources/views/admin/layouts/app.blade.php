@use(App\Enums\PreferenceKey)

@php
    $user = auth()->user();
    $assetsVersion = config('settings.assets_version');
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="_token" content="{{ csrf_token() }}" id="csrf">
    <meta name="login-url" content="{{ route('admin.auth.login') }}">
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

    <link rel="icon" href="{{ asset('assets/theme/img/favicon.ico') . '?v=' . $assetsVersion }}"
        type="image/x-icon">


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

        #action-blocker {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0); /* Fully transparent */
            z-index: 9999;
            cursor: not-allowed;
        }
    </style>

    @stack('style')

    <x-admin.microsoft-clarity />

    <script>
        // if ('serviceWorker' in navigator) {
        //     navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
        //         .then(() => console.log("Service Worker registered"))
        //         .catch(error => console.log("Service Worker registration failed:", error));
        // }
    </script>

</head>


<body class="layout-fluid">

    <script src="{{ asset('assets/theme/js/demo-theme.min.js') }}"></script>

    <input type="text" style="display:none">

    @include('admin.layouts.components.overlay-loader')

    @if ($filters ?? false)
        @include('admin.layouts.components.filter-offcanvas', ['filters' => $filters])
    @endif

    <div class="page">

        @if ($user->preferencesMap->get(PreferenceKey::MENU_LAYOUT->value, 'vertical') === 'vertical')
            @include('admin.layouts.components.sidebar')
        @endif

        @include('admin.layouts.components.header')

        @if ($user->preferencesMap->get(PreferenceKey::MENU_LAYOUT->value, 'vertical') === 'horizontal')
            @include('admin.layouts.components.navbar')
        @endif

        <div class="page-wrapper">

            <div class="page-header d-print-none">
                <div class="container-xl">

                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <a role="button" id="backButton" class="me-2" style="display:none;">
                                    <i class="ti ti-arrow-left me-1 text-white bg-primary p-3 rounded-circle"></i>
                                </a>
                                <div>
                                    {!! $breadcrumbs ?? '' !!}
                                    @isset($pageTitle)
                                        <h2 class="page-title mt-2">
                                            {{ $pageTitle }}
                                        </h2>
                                    @endisset
                                </div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list">
                                @if ($filters ?? false)
                                    <div class="page-btn-parent">
                                        <button class="btn btn-outline-warning d-none d-sm-inline-block data-filter-btn"
                                            data-bs-toggle="offcanvas" href="#filter-offcanvas"
                                            aria-controls="filter-offcanvas">
                                            <i class="ti ti-filter me-1"></i>
                                            Filters
                                        </button>
                                        <button class="btn d-sm-none btn-icon data-filter-btn" aria-label="Filters"
                                            data-bs-toggle="offcanvas" href="#filter-offcanvas"
                                            aria-controls="filter-offcanvas">
                                            <i class="ti ti-filter"></i>
                                        </button>
                                    </div>
                                @endif
                                @foreach ($buttons ?? [] as $button)
                                    @if ($button !== null)
                                        @php
                                            $invisible = isset($button['invisible']) && $button['invisible'];
                                        @endphp
                                        <div class="page-btn-parent"
                                            style="{{ $invisible ? ' display: none; ' : '' }}">
                                            <a href="{{ $button['url'] ?? 'javascript:;' }}"
                                                class="d-none d-sm-inline-block">
                                                <button
                                                    class="btn btn-{{ $button['variant'] ?? 'primary' }} {{ $button['class'] ?? '' }}">
                                                    @isset($button['icon'])
                                                        <i class="{{ $button['icon'] }} me-2"></i>
                                                    @endisset
                                                    {{ $button['label'] }}
                                                </button>
                                            </a>
                                            @if (isset($button['icon']))
                                                <a href="{{ $button['url'] ?? 'javascript:;' }}"
                                                    class="btn btn-{{ $button['variant'] ?? 'primary' }} {{ $button['class'] ?? '' }} d-sm-none btn-icon"
                                                    aria-label="{{ $button['label'] }}">
                                                    <i class="{{ $button['icon'] }}"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">

                    @include('admin.layouts.components.no-internet')
                    @include('admin.layouts.components.app-mode-alert')
                    @include('admin.layouts.components.user-login-alert')
                    

                    @yield('content')

                </div>
            </div>

            @include('admin.layouts.components.footer')

            <form style="display: hidden;" action="{{ route('admin.auth.handle-logout') }}" method="POST"
                id="logoutPost">
                @csrf
            </form>
        </div>
    </div>

    <script type="text/javascript" src="{{ asset('assets/theme/js/tabler.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-notify/dist/simple-notify.min.js"></script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/plugins/notyf/notyf.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/common/js/script.js') . "?v={$assetsVersion}" }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/script.js') . "?v={$assetsVersion}" }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/prefs.js') }}"></script>

    @stack('script')

    @include('admin.layouts.components.flash')

    @include('admin.layouts.components.sounds')

    <script>
        window.preferences = @json(auth()->user()->preferencesMap);
    </script>

    <script>
        document.addEventListener("click", function(event) {
            FFSound.click();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.dt-search input').attr('placeholder', 'Search...')
            Pref.apply(window.preferences);
        });
    </script>
    <script>
        function pageButtonVisibility(selector, show) {
            const target = $(selector).closest('div.page-btn-parent');
            show ? target.show() : target.hide();
        }

        $(document).ready(function() {
            const loginPageUrl = @js(route('admin.auth.login'));
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

    <script>
        async function isOfficeTiming() {
            try {
                const response = await $.ajax({ url: '{{ route('admin.office.is-office-timing') }}', method: 'GET' });
                return response.value;
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }
    </script>

</body>

</html>
