@php /** @var \App\DTO\DashboardStats $stat */ @endphp

<h2 class="mb-2 fw-normal"><i class="ti ti-user-circle"></i> User Stats</h2>
    
<div class="row">

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index') }}">
            <x-admin.widget.simple-stat variant='primary' icon='ti ti-users' title='Total Users' :value="$stat->totalUsers" />
        </a>
    </div>

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index', ['filter_status' => true]) }}">
            <x-admin.widget.simple-stat variant='success' icon='ti ti-user-check' title='Active Users'
            :value="$stat->totalActiveUsers" />
        </a>
    </div>

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index', ['filter_logged_in' => true]) }}">
            <x-admin.widget.simple-stat variant='info' icon='ti ti-login' title='Logged In Users' :value="$stat->totalLoggedInUsers" />
        </a>
    </div>

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index', ['filter_role' => config('settings.sales_role_id'), 'filter_status' => 1]) }}">
            <x-admin.widget.simple-stat variant='warning' icon='ti ti-report-money' title='Sales Users' :value="$stat->totalSalesUsers" />
        </a>
    </div>

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index', ['filter_role' => config('settings.drafting_role_id'), 'filter_status' => 1]) }}">
            <x-admin.widget.simple-stat variant='purple' icon='ti ti-mail' title='Drafting Users' :value="$stat->totalDraftingUsers" />
        </a>
    </div>

    <div class="col-xxl-2 col-xl-4 col-md-6 mb-4">
        <a href="{{ route('admin.users.index', ['filter_role' => config('settings.service_role_id'), 'filter_status' => 1]) }}">
            <x-admin.widget.simple-stat variant='teal' icon='ti ti-phone' title='Service Users' :value="$stat->totalServiceUsers" />
        </a>
    </div>

</div>