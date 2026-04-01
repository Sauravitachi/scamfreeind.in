@use(App\Constants\Permission)

<header class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <div class="row flex-fill align-items-center">
                    <div class="col">
                        <!-- BEGIN NAVBAR MENU -->
                        <ul class="navbar-nav">

                            @foreach (\App\View\Builders\AdminSidebar::menu(user: Auth::user())->get() as $m)
                                <li class="nav-item {{ $m->hasSubmenu ? 'dropdown' : '' }}"
                                    data-url="{{ $m->url }}">
                                    <a class="nav-link {{ $m->hasSubmenu ? 'dropdown-toggle' : '' }}"
                                        href="{{ $m->url }}"
                                        @if ($m->hasSubmenu) data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside" role="button" aria-expanded="false" @endif>
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="{{ $m->icon ?? '' }} h2"></i>
                                        </span>
                                        <span class="nav-link-title"> {{ $m->title }} </span>
                                    </a>

                                    @if ($m->hasSubmenu)
                                        <div class="dropdown-menu">
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
                        <!-- END NAVBAR MENU -->
                    </div>
                </div>

                @canany([Permission::NOTIFICATION_LIST->value, Permission::NOTIFICATION_LIST_SELF->value])
                    @include('admin.layouts.components.notifications')
                @endcanany

                @include('admin.layouts.components.profile-dropdown')

            </div>
        </div>
    </div>
</header>
