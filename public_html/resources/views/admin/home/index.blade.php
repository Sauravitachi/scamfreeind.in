@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(App\Constants\Permission)

@extends('admin.layouts.app', [
    'breadcrumbs' => Breadcrumbs::render('admin.dashboard'),
    'pageTitle' => 'Home',
])

@push('script')
    <script>
        var dashboardUrl = "{{ route('admin.home') }}";

        var DASHBOARD = {
            salesUsers: @js($salesUsers),
            draftingUsers: @js($draftingUsers),
            ajax: async function(query, otherData) {
                let resData = null;
                await runAjax({
                    url: dashboardUrl,
                    method: 'GET',
                    data: { ...otherData, query },
                    success: function(res) {
                        resData = res?.data;
                    }
                });
                return resData;
            }
        };
    </script>
@endpush

@include('admin.layouts.components.select2')
@include('admin.layouts.components.apex-charts')

@section('content')

    @can(Permission::DASHBOARD_USER_STATS)
        @include('admin.home.components.user-stats')
    @endcan

    @can(Permission::DASHBOARD_SCAM_STATS)
        @include('admin.home.components.scam-stats')
    @endcan

    
    @canany([
        Permission::DASHBOARD_TOTAL_SCAMS_CHART
    ])
        <div class="row mb-4">
            @can(Permission::DASHBOARD_TOTAL_SCAMS_CHART)
                <div class="col-lg-4">
                    @include('admin.home.components.scam-count')
                </div>
                @endcan
            </div>
            @endcanany
            
            <div class="row gy-3 mb-4">

        @can(Permission::DASHBOARD_SALES_STATUS_STATS)
            <div class="col-xl-6">
                @include('admin.home.components.user-scam-report', [
                    'type' => 'sales',
                    'users' => $salesUsers
                ])
            </div>
        @endcan

        @can(Permission::DASHBOARD_DRAFTING_STATUS_STATS)
            <div class="col-xl-6">
                @include('admin.home.components.user-scam-report', [
                    'type' => 'drafting',
                    'users' => $draftingUsers
                ])
            </div>
        @endcan
        
    </div>
    
    @include('admin.home.components.sales-user-target')
    @canany([
        Permission::DASHBOARD_CUSTOMERS_BY_REGION_CHART,
        Permission::DASHBOARD_SCAMS_BY_SOURCE_CHART,
        Permission::DASHBOARD_RECENT_SCAMS
    ])
        <div class="row gy-3">

            @can(Permission::DASHBOARD_CUSTOMERS_BY_REGION_CHART)
                <div class="col-xxl-4 col-lg-6">
                    @include('admin.home.components.customers-by-region')
                </div>
            @endcan

            @can(Permission::DASHBOARD_SCAMS_BY_SOURCE_CHART)
                <div class="col-xxl-3 col-lg-6">
                    @include('admin.home.components.scams-by-source')
                </div>    
            @endcan

            @can(Permission::DASHBOARD_RECENT_SCAMS)
                <div class="col-xxl-5 col-lg-6">
                    @include('admin.home.components.recent-scams')
                </div>    
            @endcan

        </div>
    @endcanany

    
@endsection