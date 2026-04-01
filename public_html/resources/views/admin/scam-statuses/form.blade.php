@use(Proengsoft\JsValidation\Facades\JsValidatorFacade)

@php
    $scamStatus ??= null;
    $isUpdate = !!$scamStatus;
@endphp

@include('admin.layouts.components.select2')

<form action="{{ $actionUrl }}" method="POST" id="scam-status-form">
    @csrf
    @method($method)
    <div class="row mb-3">
        <div class="col-lg-6">
            <x-admin.input type='number' min='1' name='index' label='Index' placeholder='Enter index' :value="$scamStatus?->index"
                required />
        </div>
        <div class="col-lg-6">
            <x-admin.input name='slug' label='Scam Status Slug' placeholder='Enter Scam Status Slug' :value="$scamStatus?->slug"
                required />
        </div>
        <div class="col-lg-6">
            <x-admin.input name='title' label='Scam Status Title' placeholder='Enter Scam Status Title'
                :value="$scamStatus?->title" required />
        </div>
        <div class="col-lg-6">
            <x-admin.select name='type' label='Status Type' class="select2 {{ $scamStatus ? 'keep-disabled' : '' }}" :disabled="!!$scamStatus" required>
                <option value="" selected disabled>Select Type</option>
                @foreach ($scamStatusTypes as $type)
                    <option value="{{ $type->value }}" @selected($scamStatus && $scamStatus->type == $type)>{{ $type->label() }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <div class="col-lg-6">
            <x-admin.input type="number" name='notify_after_days' label='Notify After Days' placeholder='Enter days'
                :value="$scamStatus?->notify_after_days" />
        </div>
        <div class="col-lg-6">
            <x-admin.select name='customer_enquiry_notify_role_id' label='Customer Enquiry Notify To Role' class="select2" :options="$roles->pluck('name', 'id')->toArray()" placeholder="Select Role" :selected="$scamStatus?->customer_enquiry_notify_role_id" />
        </div>
        <div class="col-lg-6">
            <x-admin.input type="number" name='cap_scams' label='Cap cases' placeholder='Enter cases count'
                :value="$scamStatus?->cap_scams" />
        </div>
        <div class="col-lg-6">
            <x-admin.input type="number" name='cap_last_days' label='Cap Days (Last n days)' placeholder='Enter number of days'
                :value="$scamStatus?->cap_last_days" />
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="is_file_required" value="0" />
            <x-admin.checkbox name='is_file_required' label='Is File Required?' :checked="!!$scamStatus?->is_file_required" />
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="is_data_update_required" value="0" />
            <x-admin.checkbox name='is_data_update_required' label='Is Data Update Required? (for first status update only)' :checked="!!$scamStatus?->is_data_update_required" />
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="is_scam_type_update_required" value="0" />
            <x-admin.checkbox name='is_scam_type_update_required' label='Is Scam Type Update Required? (for first status update only)' :checked="!!$scamStatus?->is_scam_type_update_required" />
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="is_lock" value="0" />
            <x-admin.checkbox name='is_lock' label='Is Lock?' :checked="!!$scamStatus?->is_lock" />
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="is_approval_required" value="0" />
            <x-admin.checkbox name='is_approval_required' label='Is Approval Required?' :checked="!!$scamStatus?->is_approval_required" />
        </div>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <input type="hidden" name="bypass_enquiry" value="0" />
            <x-admin.checkbox name='bypass_enquiry' label='Bypass Enquiry?' :checked="!!$scamStatus?->bypass_enquiry" />
        </div>
    </div>
    <hr />
    <div>
        <div>
            <input type="hidden" name="is_freezable" value="0" />
            <x-admin.checkbox name='is_freezable' id="is-freezable-chk" label='Is Freezable?' value='1' :checked="!!$scamStatus?->is_freezable" />
        </div>
        <div id="freeze-options-container" class="row mt-4" @if(!$scamStatus?->is_freezable) style="display: none;" @endif>
           <div class="col-lg-4 col-xl-3 col-xxl-2">
                <x-admin.input type="number" step="1" name='hours_to_freeze' label='Hours to freeze' placeholder='Enter hours'
                :value="$scamStatus?->hours_to_freeze" />
           </div>
           <div class="col-lg-4 col-xl-3 col-xxl-2">
                <x-admin.input type="number" step="1" name='freeze_scams_threshold' label='Freeze Scams Threshold' placeholder='Enter count'
                    :value="$scamStatus?->freeze_scams_threshold" />
            </div>
            <div class="col-lg-4 col-xl-3 col-xxl-2">
                <x-admin.input type="number" step="1" name='freeze_release_scams_threshold' label='Freeze Release Scams Threshold' placeholder='Enter count'
                    :value="$scamStatus?->freeze_release_scams_threshold" />
            </div>
        </div>
    </div>

    <hr />

        <div>
            <div>
                <input type="hidden" name="unassign_scam" value="0" />
                <x-admin.checkbox name='unassign_scam' id="unassign-scam-chk" label='Unassign Case?' value='1' :checked="!!$scamStatus?->unassign_scam" />
            </div>
            <div id="unassign-scam-options-container" class="row mt-4" @if(!$scamStatus?->unassign_scam) style="display: none;" @endif>
                <div class="col-lg-4 col-xl-3 col-xxl-2">
                    <x-admin.input type="number" step="1" min="0" name='unassign_scam_in_days' placeholder='Enter days'
                    :value="$scamStatus?->unassign_scam_in_days">
                        <x-slot:label>
                            Unassign in days <span class="text-muted">(0 = instant)</span>
                        </x-slot:label>
                    </x-admin.input>
                </div>
            </div>
        </div>

    <hr />
    
    @include('admin.scam-statuses._scam_update_fields_section')

    <div class="row mb-5" style="margin-top: 100px;">
        <div class="col-12 px-0">
            <div class="text-end">
                <x-admin.button label='Save Changes' submit />
            </div>
        </div>
    </div>
</form>

@push('script')
    {!! JsValidatorFacade::formRequest(
        \App\Http\Requests\Admin\ScamStatusRequest::class,
        '#scam-status-form',
    ) !!}
    <script>
        $(document).ready(function() {
            const isUpdate = @js($isUpdate);

            ajaxForm('#scam-status-form', {
                responseRedirect: !isUpdate,
                disableFormAfterSuccess: !isUpdate,
                handleToast: isUpdate
            });


            $('#is-freezable-chk').on('change', function() {
                $('#freeze-options-container').toggle();
            });

            $('#unassign-scam-chk').on('change', function() {
                $('#unassign-scam-options-container').toggle();
            });
            
        });
    </script>
@endpush
