@use(App\Constants\Permission)

@push('style')
    <style>
        .filter-card {
            border: 1px solid #c9c9c9;
        }
    </style>
@endpush

<div class="row" style="row-gap: 2rem;">
    <div class="col-xl-4">
        <div class="card filter-card">
            <div class="card-body pb-0 pt-3 row">
                <div class="col-lg-6">
                    <x-admin.input name='customer_name' label='Customer Name' placeholder='Enter customer name' />
                    <x-admin.checkbox name='exclude_customer_name' label='Exclude' />
                </div>
                <div class="col-lg-6">
                    <x-admin.input type='number' name='customer_mobile_number' label='Customer Mobile No.' placeholder='Enter customer mobile no.' />
                    <x-admin.checkbox name='exclude_customer_mobile_number' label='Exclude' />
                </div>
            </div>
        </div>
    </div>
    
    @canany([Permission::SCAM_TYPE_FILTER, Permission::SHOW_SCAM_SOURCE])
        <div class="col-xl-8">
            <div class="card filter-card">
                <div class="card-body pb-0 pt-3 row">
                    @can(Permission::SCAM_TYPE_FILTER)
                        <div class="col-lg-4">
                            @php($options = $scamTypes->pluck('title', 'id')->toArray())
                            <x-admin.select name='scam_type_id' label='Scam Type' class="filter-select2" :options="$options" multiple />
                            <x-admin.checkbox name='exclude_scam_type_id' label='Exclude' />
                        </div>
                    @endcan

                    @can(Permission::SHOW_SCAM_SOURCE)
                        <div class="col-lg-4">
                            <x-admin.select2-ajax name='scam_source_id' label='Source' id="filter_scam_source" :route="route('admin.scam-sources.select-search')" dropdownParent="FilterModule.$filterOffcanvasBody" minimumInputLength="0" :default="request()->input('filter_scam_source')" paginate multiple />
                            <x-admin.checkbox name='exclude_scam_source_id' label='Exclude' />
                        </div>
                    @endcan

                    @can(Permission::SCAM_CREATED_AT_FILTER)
                        <div class="col-lg-4">
                            <x-admin.input class="date_range_picker" name='created_at' label='Created At' :value="request()->input('created_at')" placeholder='Select Range' />
                            <x-admin.checkbox name='exclude_created_at' label='Exclude' />
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    @endcanany

    

    @canany([
        Permission::SALES_ASSIGNEE_FILTER,
        Permission::SALES_STATUS_FILTER,
        Permission::LAST_SALES_ASSIGNED_AT_FILTER,
        Permission::LAST_SALES_STATUS_UPDATED_AT_FILTER,
        Permission::SCAM_CREATED_AT_FILTER
    ])
        <div class="col-xl-6">
            <div class="card filter-card">
                <div class="card-body pb-0 pt-3 row">
                    <div class="col-lg-6">
                        @can(Permission::SALES_ASSIGNEE_FILTER)
                            @php($options = $salesUsers->pluck('name', 'id'))
                            @php($options = $options->prepend('UnAssigned 🔴', -1)->toArray())
                            <x-admin.select name='sales_assignee_id' label='Sales Assignee' class="filter-select2" :options="$options" :selected="request()->input('filter_sales_assignee_id')" multiple />
                            <div class="d-flex gap-3">
                                <x-admin.checkbox name='exclude_sales_assignee_id' :checked="request()->boolean('exclude_sales_assignee_id')" label='Exclude' />
                                <x-admin.checkbox name='history_sales_assignee_id' label='History' />
                            </div>
                        @endcan

                        @can(Permission::SALES_STATUS_FILTER)
                            @php($options = $scamStatuses->where('type', 'sales')->pluck('title', 'id'))
                            @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
                            <x-admin.select name='sales_status_id' label='Sales Status' class="filter-select2" :options="$options" :selected="request()->input('filter_sales_status_id')" multiple />
                            <x-admin.checkbox name='exclude_sales_status_id' label='Exclude' />
                        @endcan
                    </div>

                    <div class="col-lg-6">
                        @can(Permission::LAST_SALES_ASSIGNED_AT_FILTER)
                            <x-admin.input class="date_range_picker" name='sales_assigned_at' label='Last Sales Assgined At' :value="request()->input('sales_assigned_at')"  placeholder='Select Range' />
                            <x-admin.checkbox name='exclude_sales_assigned_at' label='Exclude' />
                        @endcan

                        @can(Permission::LAST_SALES_STATUS_UPDATED_AT_FILTER)
                            <x-admin.input class="date_range_picker" name='sales_status_updated_at' label='Last Sales Status Updated At' placeholder='Select Range' :value="request()->input('sales_status_updated_at')" />
                            <x-admin.checkbox name='exclude_sales_status_updated_at' label='Exclude' />
                        @endcan
                    </div>
                    @can(Permission::SUB_ADMIN_FILTER)
                        <div class="col-lg-6">
                            @php($options = $subAdminUsers->pluck('name', 'id'))
                            @php($options = $options->prepend('UnAssigned 🔴', -1)->toArray())
                            <x-admin.select name='sub_admin_id' label='Sub Admin' class="filter-select2" :options="$options" :selected="request()->input('filter_sub_admin_id')" multiple />
                            <x-admin.checkbox name='exclude_sub_admin_id' label='Exclude' />
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    @endcanany

    @canany([
        Permission::DRAFTING_ASSIGNEE_FILTER,
        Permission::DRAFTING_STATUS_FILTER,
        Permission::LAST_DRAFTING_ASSIGNED_AT_FILTER,
        Permission::LAST_DRAFTING_STATUS_UPDATED_AT_FILTER
    ])
    <div class="col-xl-6">
        <div class="card filter-card">
            <div class="card-body pb-0 pt-3 row">

                <div class="col-lg-6">
                    @can(Permission::DRAFTING_ASSIGNEE_FILTER)
                        @php($options = $draftingUsers->pluck('name', 'id'))
                        @php($options = $options->prepend('UnAssigned 🔴', -1)->toArray())
                        <x-admin.select name='drafting_assignee_id' label='Drafting Assignee' class="filter-select2" :options="$options" :selected="request()->input('filter_drafting_assignee_id')" multiple />
                        <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_drafting_assignee_id' :checked="request()->boolean('exclude_drafting_assignee_id')" label='Exclude' />
                            <x-admin.checkbox name='history_drafting_assignee_id' label='History' />
                        </div>
                    @endcan

                    @can(Permission::DRAFTING_STATUS_FILTER)
                        @php($options = $scamStatuses->where('type', 'drafting')->pluck('title', 'id'))
                        @php($options = $options->prepend('Without Status 🔴', -1)->toArray())
                        <x-admin.select name='drafting_status_id' label='Drafting Status' class="filter-select2" :options="$options" :selected="request()->input('filter_drafting_status_id')" multiple />
                        <x-admin.checkbox name='exclude_drafting_status_id' label='Exclude' />
                    @endcan
                </div>

                <div class="col-lg-6">
                    @can(Permission::LAST_DRAFTING_ASSIGNED_AT_FILTER)
                        <x-admin.input class="date_range_picker" name='drafting_assigned_at' label='Last Drafting Assgined At' :value="request()->input('drafting_assigned_at')" placeholder='Select Range' />
                        <x-admin.checkbox name='exclude_drafting_assigned_at' label='Exclude' />
                    @endcan

                    @can(Permission::LAST_DRAFTING_STATUS_UPDATED_AT_FILTER)
                        <x-admin.input class="date_range_picker" name='drafting_status_updated_at' label='Last Drafting Status Updated At' placeholder='Select Range' :value="request()->input('drafting_status_updated_at')" />
                        <x-admin.checkbox name='exclude_drafting_status_updated_at' label='Exclude' />
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endcanany

    <!-- @canany([Permission::SERVICE_ASSIGNEE_FILTER])
        <div class="col-xl-6">
            <div class="card filter-card">
                <div class="card-body pb-0 pt-3 row">

                    <div class="col-lg-6">
                        @can(Permission::SERVICE_ASSIGNEE_FILTER)
                            @php($options = $serviceUsers->pluck('name', 'id'))
                            @php($options = $options->prepend('UnAssigned 🔴', -1)->toArray())
                            <x-admin.select name='service_assignee_id' label='Service Assignee' class="filter-select2" :options="$options" multiple />
                            <div class="d-flex gap-3">
                                <x-admin.checkbox name='exclude_service_assignee_id' label='Exclude' />
                                <x-admin.checkbox name='history_service_assignee_id' label='History' />
                            </div>
                        @endcan
                    </div>

                    <div class="col-lg-6">
                        @can(Permission::LAST_SERVICE_ASSIGNED_AT_FILTER)
                            <x-admin.input class="date_range_picker" name='service_assigned_at' label='Last Service Assgined At' placeholder='Select Range' />
                            <x-admin.checkbox name='exclude_service_assigned_at' label='Exclude' />
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endcanany -->
    <div class="col-xl-4">
        <div class="card filter-card">
            <div class="card-body pb-0 pt-3 row">
                <div class="col-lg-6">
                    <x-admin.input type='number' name='amount_min' label='Min Amount' placeholder='Min' />
                </div>
                <div class="col-lg-6">
                    <x-admin.input type='number' name='amount_max' label='Max Amount' placeholder='Max' />
                </div>
                <div class="col-12">
                    <x-admin.checkbox name='exclude_amount_range' label='Exclude' />
                </div>
            </div>
        </div>
    </div>
    @can(Permission::STATUS_UNASSIGNED_SCAM_LIST)
        <div class="col-xl-6">
            <div class="card card-filter">
                <div class="card-body pb-0 pt-3 row">
                    <div class="col-lg-6">
                        @php($options = $salesUsers->pluck('name', 'id')->toArray())
                        <x-admin.select name='sales_status_unassigned_assignee_id' label='Sales Status Unassigned Assignee' class="filter-select2" :options="$options" multiple />
                        <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_sales_status_unassigned_assignee_id' label='Exclude' />
                            <x-admin.checkbox name='history_sales_status_unassigned_assignee_id' label='History' />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        @php($options = $scamStatuses->where('type', 'sales')->where('unassign_scam', true)->pluck('title', 'id')->toArray())
                        <x-admin.select name='sales_status_unassigned_status_id' label='Sales Unassign Status' class="filter-select2" :options="$options" :selected="request()->input('filter_sales_status_unassigned_status_id')" multiple />
                    <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_sales_status_unassigned_status_id' label='Exclude' />
                    </div>
                    </div>
                    <div class="col-lg-6">
                        <x-admin.input class="date_range_picker" name='sales_status_unassigned_at' label='Sales Status Unassigned At' placeholder='Select Range' />
                        <x-admin.checkbox name='exclude_sales_status_unassigned_at' label='Exclude' />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-filter">
                <div class="card-body pb-0 pt-3 row">
                    <div class="col-lg-6">
                        @php($options = $draftingUsers->pluck('name', 'id')->toArray())
                        <x-admin.select name='drafting_status_unassigned_assignee_id' label='Drafting Status Unassigned Assignee' class="filter-select2" :options="$options" multiple />
                        <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_drafting_status_unassigned_assignee_id' label='Exclude' />
                            <x-admin.checkbox name='history_drafting_status_unassigned_assignee_id' label='History' />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        @php($options = $scamStatuses->where('type', 'drafting')->where('unassign_scam', true)->pluck('title', 'id')->toArray())
                        <x-admin.select name='drafting_status_unassigned_status_id' label='Drafting Unassign Status' class="filter-select2" :options="$options" :selected="request()->input('filter_drafting_status_unassigned_status_id')" multiple />
                        <x-admin.checkbox name='exclude_drafting_status_unassigned_status_id' label='Exclude' />
                    </div>
                    <div class="col-lg-6">
                        <x-admin.input class="date_range_picker" name='drafting_status_unassigned_at' label='Drafting Status Unassigned At' placeholder='Select Range' />
                        <x-admin.checkbox name='exclude_drafting_status_unassigned_at' label='Exclude' />
                    </div>
                </div>
            </div>
        </div>
    @endcan
    <div class="col-xl-6">
        <div class="card filter-card">
            <div class="card-body pb-0 pt-3 row">
                <div class="col-lg-6">
                    <div>
                        <x-admin.select name='sales_status_review' label='Sales Status Review' class="filter-select2" :options="\App\Enums\ScamStatusReview::selectArray()" multiple />
                        <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_sales_status_review' label='Exclude' />
                        </div>
                    </div>
                    <div class="mt-2">
                        <x-admin.select name='sales_status_review_status' label='Sales Status Review (Status)' class="filter-select2" :options="$scamStatuses->where('is_approval_required', true)->where('type', 'sales')->pluck('title', 'id')->toArray()" multiple />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div>
                        <x-admin.select name='drafting_status_review' label='Drafting Status Review' class="filter-select2" :options="\App\Enums\ScamStatusReview::selectArray()" multiple />
                        <div class="d-flex gap-3">
                            <x-admin.checkbox name='exclude_drafting_status_review' label='Exclude' />
                        </div>
                    </div>
                    <div class="mt-2">
                        <x-admin.select name='drafting_status_review_status' label='Drafting Status Review (Status)' class="filter-select2" :options="$scamStatuses->where('is_approval_required', true)->where('type', 'drafting')->pluck('title', 'id')->toArray()" multiple />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mt-2">
        <x-admin.checkbox id="filter_unassign_all" label='All Unassigned' />
    </div>
</div>

@push('script')
    <script>
        $(document).ready(function() {

            $('#filter_unassign_all').on('change', function() {
                let value = $(this).is(':checked') ? '-1' : '';
                let fields = [
                    'sales_assignee_id',
                    'drafting_assignee_id',
                    'sales_status_id',
                    'drafting_status_id',
                    'service_assignee_id',
                    'sub_admin_id'
                ];
                
                fields.forEach(field => {
                    $('#filter-offcanvas-form').find(`select[name="${field}"]`).val(value).trigger("change");
                });
            });


        });
    </script>
@endpush
