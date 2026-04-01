<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open customer menu">
        <span class="avatar avatar-sm avatar-rounded"
            style="background-image: url({{ asset('assets/theme/customer.png') }})"></span>
        <div class="d-none d-xl-block ps-2">
            <div>
                @php
                    $customer = \App\Models\Customer::find(session('customer_id'));
                @endphp
                {{ $customer?->full_name ?? 'Customer' }}
            </div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <a href="javascript:;" onclick="$('#logoutPost').submit();" class="dropdown-item">Logout</a>
    </div>
</div>
