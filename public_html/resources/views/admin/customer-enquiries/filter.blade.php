@use(App\Enums\CustomerEnquiryStatusType)
@use(App\Enums\ScamStatusType)

<div class="row">
    @if($userType === 'admin')
        @if ($assigneeType === 'sales')
            <div class="col-12">
                @php($options = $salesUsers->pluck('name', 'id')->toArray())
                <x-admin.select name='sales_assignee_id' label='Sales Assignee' class="filter-select2" :options="$options" multiple />
                <x-admin.checkbox name='exclude_sales_assignee_id' label='Exclude' />
            </div>  
        @endif
        @if ($assigneeType === 'drafting')
            <div class="col-12">
                @php($options = $draftingUsers->pluck('name', 'id')->toArray())
                <x-admin.select name='drafting_assignee_id' label='Drafting Assignee' class="filter-select2" :options="$options" multiple />
                <x-admin.checkbox name='exclude_drafting_assignee_id' label='Exclude' />
            </div>
        @endif
    @endif
    @if ($assigneeType === 'sales')
        <div class="col-12">
            @php($options = $customerEnquiryStatuses->where('type', CustomerEnquiryStatusType::SALES)->pluck('title', 'id'))
            @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
            <x-admin.select name='sales_status_id' label='Sales Status' class="filter-select2" :options="$options" multiple />
        </div>  
    @endif
    @if ($assigneeType === 'drafting')
        <div class="col-12">
            @php($options = $customerEnquiryStatuses->where('type', CustomerEnquiryStatusType::DRAFTING)->pluck('title', 'id'))
            @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
            <x-admin.select name='drafting_status_id' label='Drafting Status' class="filter-select2" :options="$options" multiple />
        </div>
    @endif
    @if ($assigneeType === 'sales')
        <div class="col-12">
            @php($options = $scamStatuses->where('type', ScamStatusType::SALES)->pluck('title', 'id'))
            @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
            <x-admin.select name='scam_sales_status_id' label='Case Sales Status' class="filter-select2" :options="$options" multiple />
        </div>  
    @endif
    @if ($assigneeType === 'drafting')
        <div class="col-12">
            @php($options = $scamStatuses->where('type', ScamStatusType::DRAFTING)->pluck('title', 'id'))
            @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
            <x-admin.select name='scam_drafting_status_id' label='Case Drafting Status' class="filter-select2" :options="$options" multiple />
        </div>
    @endif
    @if ($assigneeType === 'sales')
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='scam_sales_status_updated_at' label='Case Sales Status Updated At' placeholder='Select Range' :value="request()->input('scam_sales_status_updated_at')" />
        </div>  
    @endif
    @if ($assigneeType === 'drafting')
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='scam_drafting_status_updated_at' label='Case Drafting Status Updated At' placeholder='Select Range' :value="request()->input('scam_drafting_status_updated_at')" />
        </div>
    @endif
    @if ($assigneeType === 'sales')
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='sales_status_updated_at' label='Status Updated At' placeholder='Select Range' />
        </div>
    @endif
    @if ($assigneeType === 'drafting')
        <div class="col-12">
            <x-admin.input class="date_range_picker" name='drafting_status_updated_at' label='Status Updated At' placeholder='Select Range' />
        </div>
    @endif
    <div class="col-lg-6">
        <x-admin.input type='number' name='scam_amount_lb' label='Scam Amount (<=)' />
    </div>  
    <div class="col-lg-6">
        <x-admin.input type='number' name='scam_amount_ub' label='Scam Amount (<=)' />
    </div>  
    <div class="col-12">
        <x-admin.select2-ajax name='scam_source_id' label='Source' id="filter_scam_source" :route="route('admin.scam-sources.select-search')" dropdownParent="FilterModule.$filterOffcanvasBody" minimumInputLength="0" paginate />
    </div>
    <div class="col-12">
        <x-admin.input class="date_range_picker" name='created_at' label='Created At' placeholder='Select Range' />
    </div>
</div>