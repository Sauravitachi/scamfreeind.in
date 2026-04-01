<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('admin.home') }}" class="text-white mt-2">
                {{ config('settings.brand_name') }}
            </a>
        </div>
        <div class="navbar-nav flex-row d-lg-none">
            {{-- <div class="d-flex d-lg-none me-2">
                <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" data-bs-toggle="tooltip"
                    data-bs-placement="bottom" aria-label="Enable dark mode" data-bs-original-title="Enable dark mode">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
                    </svg>
                </a>
                <a href="?theme=light" class="nav-link px-0 hide-theme-light" data-bs-toggle="tooltip"
                    data-bs-placement="bottom" aria-label="Enable light mode"
                    data-bs-original-title="Enable light mode">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                        <path
                            d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7">
                        </path>
                    </svg>
                </a>
            </div> --}}
            @include('admin.layouts.components.profile-dropdown')
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">

                @foreach (\App\View\Builders\AdminSidebar::menu(user: Auth::user())->get() as $m)
                    <li class="nav-item mt-1 {{ $m->hasSubmenu ? 'dropdown' : '' }}" data-url="{{ $m->url }}">
                        <a class="nav-link {{ $m->hasSubmenu ? 'dropdown-toggle' : '' }}" href="{{ $m->url }}"
                            @if ($m->hasSubmenu) data-bs-toggle="dropdown"
                                        data-bs-auto-close="false" role="button" aria-expanded="false" @endif>
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="{{ $m->icon ?? '' }} h2"></i>
                            </span>
                            <span class="nav-link-title">
                                {{ $m->title }}
                            </span>
                        </a>

                        @if ($m->hasSubmenu)
                            <div class="dropdown-menu sidebar-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        @foreach ($m->submenu as $sm)
                                            <a class="dropdown-item" href="{{ $sm->url }}">
                                                {{ $sm->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach

            </ul>
        </div>
    </div>
</aside>
