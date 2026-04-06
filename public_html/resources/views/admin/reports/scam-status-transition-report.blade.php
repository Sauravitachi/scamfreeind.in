@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.reports.scam-status-transition-report'),
])

@include('admin.layouts.components.datatable', [
    'fullFeatures' => true
])
@include('admin.layouts.components.select2')
@include('admin.layouts.components.datepicker')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h3 class="fw-normal card-title">Generate Scam Status Transition Report</h3>

            <form id="scam-status-report-form">
                <div class="row">
                    <div class="col-md-3 col-12">
                        <x-admin.input class="date_range_picker" name='date' label='Date Range' placeholder='Select Date Range' value="{{ now()->toDateString() . ' to ' . now()->toDateString() }}" />
                    </div>
                    <div class="col-md-3 col-12">
                        @php($salesOptions = $salesStatuses->pluck('title', 'title')->prepend('Without Status 🔴', 'Without Status')->toArray())
                        <x-admin.select name='sales_status' label='Sales Status' class="select2" :options="$salesOptions" placeholder="All Sales" :selected="request()->input('sales_status')"/>
                    </div>
                    <div class="col-md-3 col-12">
                        @php($draftingOptions = $draftingStatuses->pluck('title', 'title')->prepend('Without Status 🔴', 'Without Status')->toArray())
                        <x-admin.select name='drafting_status' label='Drafting Status' class="select2" :options="$draftingOptions" placeholder="All Drafting" :selected="request()->input('drafting_status')"/>
                    </div>
                    <div class="col-md-3 col-12">
                        @if (auth()->user()->hasAnyRole(['Admin', 'Super Admin', 'Manager', 'Sub Admin', 'Product Head', 'MIS', 'Auditor', 'Tech Team']))
                            <x-admin.select name='causer_id' label='User/Agent' class="select2" :options="$users->pluck('name', 'id')->toArray()" placeholder="All Users" />
                        @else
                            <input type="hidden" name="causer_id" value="{{ auth()->id() }}">
                        @endif
                    </div>
                </div>
                <div class="text-end mt-2">
                    <x-admin.button label='Generate Report' icon='ti ti-search' id="generate-btn" />
                </div>
            </form>
        </div>
    </div>

    <div id="summary-cards" class="row mb-3">
        <!-- Summary cards will be injected here -->
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Transitions List</h5>
            <table id="transitions-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Category</th>
                        <th>User</th>
                        <th>From Status</th>
                        <th>To Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            const $form = $('#scam-status-report-form');
            const $summaryContainer = $('#summary-cards');
            let dataTable = $('#transitions-table').DataTable({
                responsive: true,
                order: [[5, 'desc']], 
                columns: [
                    { data: 'customer_number' },
                    { data: 'status_type' },
                    { data: 'user' },
                    { data: 'from' },
                    { data: 'to' },
                    { data: 'time' }
                ]
            });

            function loadReport() {
                const formData = {
                    date: $form.find('[name="date"]').val(),
                    sales_status: $form.find('[name="sales_status"]').val(),
                    drafting_status: $form.find('[name="drafting_status"]').val(),
                    causer_id: $form.find('[name="causer_id"]').val(),
                };

                overlayLoader.show();

                $.ajax({
                    url: '/api/scam-status-report',
                    method: 'GET',
                    data: formData,
                    success: function(res) {
                        // Update Summary Cards
                        $summaryContainer.empty();
                        
                        // Total Changes Card
                        $summaryContainer.append(`
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body py-2">
                                        <h6 class="card-title mb-1">Total Transitions</h6>
                                        <h4 class="mb-0">${res.total_changes}</h4>
                                    </div>
                                </div>
                            </div>
                        `);

                        // Detailed transition counts
                        Object.entries(res.summary).forEach(([transition, count]) => {
                            $summaryContainer.append(`
                                <div class="col-md-3 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body py-2">
                                            <h6 class="card-title mb-1 text-primary">${transition}</h6>
                                            <h4 class="mb-0 text-dark">${count}</h4>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });

                        // Update Table
                        dataTable.clear().rows.add(res.changes).draw();
                    },
                    error: function(err) {
                        toastr.error('Failed to fetch report data.');
                    },
                    complete: function() {
                        overlayLoader.hide();
                    }
                });
            }

            $('#generate-btn').on('click', function(e) {
                e.preventDefault();
                loadReport();
            });

            // Initial load
            loadReport();
        });
    </script>
@endpush
