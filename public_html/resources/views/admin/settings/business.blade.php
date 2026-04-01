@use(Diglactic\Breadcrumbs\Breadcrumbs)
@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)
@use(App\Http\Requests\Admin\BusinessSettingsRequest)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.settings.business'),
])

@include('admin.layouts.components.datepicker')
@include('admin.layouts.components.select2')

@section('content')
<form id="bussiness-settings-form" action="{{ route('admin.settings.business') }}" method="POST">
    @csrf
    <div class="row gy-3">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        Office Timings
                    </h4>
                    <div class="row">
                        <div class="col-lg-6">
                            <x-admin.input label='Start Time' name='office_start_time' class='time_picker' :value="$settings->get('office_start_time')?->value"  />
                        </div>
                        <div class="col-lg-6">
                            <x-admin.input label='End Time' name='office_end_time' class="time_picker" :value="$settings->get('office_end_time')?->value" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        Freeze Settings for 'null' Status <span class="text-secondary">(Sales)</span>
                    </h4>
                    <div class="row">
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Hours to freeze' name='hours_to_freeze_sales_null' placeholder='Enter hours' :value="$settings->get('hours_to_freeze_sales_null')?->value"  />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Freeze Threshold' name='freeze_sales_null_threshold' placeholder='Enter number' :value="$settings->get('freeze_sales_null_threshold')?->value" />
                        </div>
                        <div class="col-lg-4"> 
                            <x-admin.input type='number' step='1' label='Freeze Release Threshold' name='freeze_sales_null_release_threshold' placeholder='Enter number' :value="$settings->get('freeze_sales_null_release_threshold')?->value" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        Freeze Settings for 'null' Status <span class="text-secondary">(Drafting)</span>
                    </h4>
                    <div class="row">
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Hours to freeze' name='hours_to_freeze_drafting_null' placeholder='Enter hours' :value="$settings->get('hours_to_freeze_drafting_null')?->value"  />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Freeze Threshold' name='freeze_drafting_null_threshold' placeholder='Enter number' :value="$settings->get('freeze_drafting_null_threshold')?->value" />
                        </div>
                        <div class="col-lg-4"> 
                            <x-admin.input type='number' step='1' label='Freeze Release Threshold' name='freeze_drafting_null_release_threshold' placeholder='Enter number' :value="$settings->get('freeze_drafting_null_release_threshold')?->value" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        Enquiry/Escalation Freeze <span class="text-secondary">(Drafting)</span>
                    </h4>
                    <div class="row">
                        <div class="col-lg-6">
                            <x-admin.input type='number' step='1' label='Hours to freeze' name='hours_to_freeze_enquiries' placeholder='Enter hours' :value="$settings->get('hours_to_freeze_enquiries')?->value"  />
                        </div>
                        <div class="col-lg-6">
                            <x-admin.input type='number' step='1' label='Starting Relaxation Hours' name='starting_enquiries_relaxation_hours' placeholder='Enter hours' :value="$settings->get('starting_enquiries_relaxation_hours')?->value"  />
                        </div>
                        <div class="col-lg-6">
                            <x-admin.input type='number' step='1' label='Freeze Threshold' name='freeze_enquiry_threshold' placeholder='Enter number' :value="$settings->get('freeze_enquiry_threshold')?->value" />
                        </div>
                        <div class="col-lg-6"> 
                            <x-admin.input type='number' step='1' label='Freeze Release Threshold' name='freeze_enquiry_release_threshold' placeholder='Enter number' :value="$settings->get('freeze_enquiry_release_threshold')?->value" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title">
                        Auto Case Assign <span class="text-secondary">(Sales)</span>
                    </h4>
                    <div class="row">
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Theshold Case Count' name='sales_auto_case_assign:threshold_case_count' placeholder='Enter hours' :value="$settings->get('sales_auto_case_assign:threshold_case_count')?->value"  />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='Achieve in hours' name='sales_auto_case_assign:achieve_in_hours' placeholder='Enter number' :value="$settings->get('sales_auto_case_assign:achieve_in_hours')?->value" />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.input type='number' step='1' label='New cases count' name='sales_auto_case_assign:new_cases_count' placeholder='Enter number' :value="$settings->get('sales_auto_case_assign:new_cases_count')?->value" />
                        </div>
                        <div class="col-lg-6">
                            <x-admin.input label='Assign Fresh Cases From Date (Empty means - all time)' class='date_range_picker' name='sales_auto_case_assign:fresh_cases_date_range' :value="$settings->get('sales_auto_case_assign:fresh_cases_date_range')?->value" />
                        </div>
                        <div class="col-lg-6">
                            <x-admin.input label='Cases (Scam Amount >=) - Empty means no limitation' type='' name='sales_auto_case_assign:lb_scam_amount' placeholder='Enter amount' :value="$settings->get('sales_auto_case_assign:lb_scam_amount')?->value" />
                            <x-admin.checkbox name='sales_auto_case_assign:allow_null_amount' label='Allow Null Amount' value='1' :checked="$settings->get('sales_auto_case_assign:allow_null_amount')?->value" />
                        </div>
                        <div class="col-12">
                            <x-admin.select label='Missed Assign Notify To (Admin Roles)' name="sales_auto_case_assign:missed_assign_notify_to_roles[]" id="missed_assign_notify_to_roles" class="select2" :options="$roles->where('is_admin', true)->pluck('name', 'id')->toArray()" multiple />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-end mt-3">
        <x-admin.button label='Save Changes' submit />
    </div>
</form>
@endsection

@push('script')
    {!! JsValidatorFacade::formRequest(BusinessSettingsRequest::class, '#bussiness-settings-form') !!}

    <script>
        $(document).ready(function() {

            ajaxForm('#bussiness-settings-form', {
                responseRedirect: true,
                disableFormAfterSuccess: true,
                handleToast: true
            });

            const missed_assign_notify_to_roles_values = @js(json_decode($settings->get('sales_auto_case_assign:missed_assign_notify_to_roles')?->value ?? '[]'));
            $('#missed_assign_notify_to_roles').val(missed_assign_notify_to_roles_values).trigger('change');

        });
    </script>
@endpush
