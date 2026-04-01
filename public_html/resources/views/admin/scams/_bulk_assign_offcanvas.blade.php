<div class="offcanvas offcanvas-end" tabindex="-1" id="bulk-assign-offcanvas" style="width: 900px;">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title">Bulk Assign Scams</h2>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    </div>
</div>

@section('bulk_assign_body_section')
    <div>
        <form action="{{ route('admin.scams.bulk-assign-users') }}" id="bulk-assign-form" method="POST">
            <div class="row mb-3">
                <div class="col-12">
                    <h3>
                        Sales Details
                    </h3>
                </div>
                <div class="col-lg-6">
                    <x-admin.select name='sales_assignee_id' id="sales_assignee_bulk_select" label='Sales Assignee' class="select2" placeholder="Same as current 🟢" />
                </div>
                <div class="col-lg-6">
                    @php($options = $scamStatuses->where('type', 'sales')->where('status_update_fields_exists', 0))
                    @php($options = $options->pluck('title', 'id')->prepend('Select', 0)->toArray())
                    <x-admin.select name='sales_status_id' label='Sales Status' class="select2" :options="$options"
                        placeholder="Same as current 🟢" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <h3>
                        Drafting Details
                    </h3>
                </div>
                <div class="col-lg-6">
                    <x-admin.select name='drafting_assignee_id' id="drafting_assignee_bulk_select" label='Drafting Assignee' class="select2" placeholder="Same as current 🟢" />
                </div>
                <div class="col-lg-6">
                    @php($options = $scamStatuses->where('type', 'drafting')->where('status_update_fields_exists', 0))
                    @php($options = $options->pluck('title', 'id')->prepend('Select', 0)->toArray())
                    <x-admin.select name='drafting_status_id' label='Drafting Status' class="select2" :options="$options"
                        placeholder="Same as current 🟢" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-6">
                    <x-admin.select name='sub_admin_id' id="sub_admin_bulk_select" label='Sub Admin' class="select2" placeholder="Same as current 🟢" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <h3>
                        Source Details
                    </h3>
                </div>
                <div class="col-lg-6">
                    @php($options = $scamSources->pluck('title', 'id')->prepend('Select', 0)->toArray())
                    <x-admin.select name='source_id' id="source_filter" label='Source' class="select2" :options="$options" placeholder="Same as current 🟢" />
                </div>
            </div>
            <div class="alerts-container sales-assignee-alerts-container"></div>
            <div class="alerts-container drafting-assignee-alerts-container"></div>
            <div class="text-end">
                <x-admin.button label='Assign' icon='ti ti-user-plus' submit />
            </div>
        </form>
    </div>
@endsection

@push('script')
    {!! js_validation_custom_event(
        formRequestClass: \App\Http\Requests\Admin\BulkAssignUserToScamRequest::class,
        formSelector: '#bulk-assign-form',
        eventTargetSelector: '#bulk-assign-offcanvas',
        event: 'show.bs.offcanvas',
    ) !!}
    <script>
        
        function getValidAssignSelect(users, type) {

            const filteredUsers = users.filter(user => user.id !== null);

            return [{id: 0, text: 'Select'} ,...filteredUsers.map(user => ({id: user.id, text: user.name, disabled: !user.status }))];

        }

        function fetchBulkAssigneeValidityStatus(assigneeId) {  
            

            if(!assigneeId || assigneeId == 0) return;

            const url = "{{ route('admin.users.assignee-status', ':id') }}".replace(':id', assigneeId);

            runAjax({
                url: url,
                method: 'GET',
                beforeSend: function(res) {
                    disable_form($('#bulk-assign-form'));
                },
                success: function(res) {
                    console.log(res);
                    const userType = res.data?.user_type;
                    const alerts = res.data?.alerts ?? [];

                    $alertsContainer = $('#bulk-assign-offcanvas').find(`.${userType}-assignee-alerts-container`);

                    if(alerts.length > 0) {
                        alerts.forEach(function(alertOptions) {
                            const alertHtml = AdminHtml.alert(alertOptions);
                            $alertsContainer.append(alertHtml);
                            console.log($alertsContainer, alertHtml);
                        });
                    } else {
                        $alertsContainer.empty();
                    }
                   
                },
                complete: function() {
                    enable_form($('#bulk-assign-form'));
                }
            });
        }

        function assignBulk() {

            const selectedScams = dtSelectedRows(dtTable, 'id');

            if (selectedScams.length <= 0) {
                toast.open({
                    type: 'warning',
                    message: 'No records are selected!'
                });
                return;
            }

            const body = @js(view()->yieldContent('bulk_assign_body_section'));
            const $offcanvas = $('#bulk-assign-offcanvas');
            const $offcanvasBody = $offcanvas.find(".offcanvas-body");
            $offcanvasBody.html(body);

            ajaxForm('#bulk-assign-form', {
                handleToast: true,
                formData: function() {
                    const fd = new FormData(document.querySelector('#bulk-assign-form'));
                    selectedScams.forEach((item, index) => fd.append('scams[]', item));
                    return fd;
                },
                beforeSend: function() {
                    overlayLoader.show();
                },
                success: function() {
                    pageButtonVisibility('.__bulk_assign_btn', false);
                    dtTable.draw(false);
                    $offcanvas.offcanvas('hide');
                },
                complete: function() {
                    overlayLoader.hide();
                }
            });

            const assigneeConfigs = [
                { selector: '#sales_assignee_bulk_select', users: salesUsers, role: 'sales' },
                { selector: '#drafting_assignee_bulk_select', users: draftingUsers, role: 'drafting' },
                { selector: '#sub_admin_bulk_select', users: subAdminUsers, role: 'sub_admin' }
            ];

            assigneeConfigs.forEach(({ selector, users, role }) => {
                const $select = $offcanvasBody.find(selector);
                initSelect2($select, {
                    data: getValidAssignSelect(users, role),
                    dropdownParent: $offcanvasBody,
                    templateResult: disabledLineSelect2TemplateResult
                });
            });

            // Status select2 initialization
            initSelect2($offcanvasBody.find('select[name="sales_status_id"], select[name="drafting_status_id"], select[name="source_id"]'), {
                dropdownParent: $offcanvasBody
            });


            $offcanvas.offcanvas('show');
        }

        $(document).ready(function() {

            // Bulk Assign button click handler
            $('.__bulk_assign_btn').on('click', assignBulk);

        });
    </script>
@endpush
