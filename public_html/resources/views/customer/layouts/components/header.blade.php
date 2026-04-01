@use(App\Constants\Permission)
@use(App\Enums\PreferenceKey)

<header class="navbar navbar-expand-md modern-header">
    <div class="container-xl">
        <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('customer.home.index') }}" class="mt-2" style="color:#fff;font-weight:bold;letter-spacing:1px;">
                {{ config('settings.brand_name') }}
            </a>
        </div>
        <div class="navbar-nav flex-row order-md-last">
            @include('customer.layouts.components.profile-dropdown')
        </div>
    </div>
</header>
