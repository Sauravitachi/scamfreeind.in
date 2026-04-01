@php /** @var \App\DTO\DashboardStats $stat */ @endphp

@php $todayString = today()->toDateTimeString() @endphp

<h2 class="mb-2 fw-normal"><i class="ti ti-briefcase"></i> Case Stats</h2>

<div class="row row-cards mb-4">
    
    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index') }}">
            <x-admin.widget.simple-stat-2 :value="$stat->totalScams" icon='ti ti-asterisk' color-class="bg-primary" label="Total Cases" />
        </a>
    </div>

    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index', ['sales_assignee_id' => -1, 'exclude_sales_assignee_id' => true]) }}">
            <x-admin.widget.simple-stat-2 :value="$stat->totalSalesAssignedScams" icon='ti ti-report-money' color-class="bg-info" label="Total Sales Assigned Cases" />
        </a>
    </div>

    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index', ['drafting_assignee_id' => -1, 'exclude_drafting_assignee_id' => true]) }}">
            <x-admin.widget.simple-stat-2 :value="$stat->totalDraftingAssignedScams" icon='ti ti-pencil-check' color-class="bg-warning" label="Total Drafting Assigned Cases" />
        </a>     
    </div>



    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index', ['created_at' => $todayString]) }}">
            <x-admin.widget.simple-stat-2 :value="$stat->todaysScams" icon='ti ti-square-asterisk' color-class="bg-secondary" label="Today's Cases" />
        </a>     
    </div>

    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index', ['sales_assignee_id' => -1, 'exclude_sales_assignee_id' => true, 'sales_assigned_at' => $todayString]) }}">
            <x-admin.widget.simple-stat-2 :value="$stat->todaysSalesAssignedScams" icon='ti ti-select' color-class="bg-danger" label="Today's Sales Assigned Cases" />
        </a>     
    </div>

    <div class="col-xxl-2 col-lg-3 col-md-6">
        <a href="{{ route('admin.scams.index', ['drafting_assignee_id' => -1, 'exclude_drafting_assignee_id' => true, 'drafting_assigned_at' => $todayString]) }}">
            <x-admin.widget.simple-stat-2 :value="$stat->todaysDraftingAssignedScams" icon='ti ti-pencil' color-class="bg-dark" label="Today's Drafting Assigned Cases" />
        </a>     
    </div>
    
</div>