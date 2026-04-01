<div class="card">
    <div class="card-body">
        <div class="row mb-5" style="row-gap: 2rem;">
            <div class="col-md-6">
                <div class="fs-4 fw-bold mb-2">
                    Customer Details
                </div>
                <div class="fs-3">
                    <div class="h3 mt-3">
                        @if ($trackId = $customerEnquiry->customer->track_id)
                            <div class="mt-1">
                                <i class="ti ti-id-badge"></i>
                                <span>#{{ $trackId }}</span>
                            </div>
                        @endif
                        @if ($fullName = $customerEnquiry->customer->full_name)
                            <div class="mt-1">
                                <i class="ti ti-user-filled"></i>
                                <span>{{ $fullName }}</span>
                            </div>
                        @endif
                        @if ($phoneNumber = $customerEnquiry->customer->full_phone_number)
                            <div class="mt-1">
                                <i class="ti ti-phone-filled"></i>
                                {{ $phoneNumber }}
                            </div>
                        @endif
                        @if ($email = $customerEnquiry->customer->email)
                            <div class="mt-1">
                                <i class="ti ti-mail-filled"></i>
                                {{ $email }}
                            </div>
                        @endif
                        @if ($country = $customerEnquiry->customer->country_with_emoji)
                            <div class="mt-1">
                                {{ $country }}
                            </div>
                        @endif
                        @if ($userType === 'admin' && $registeredAt = $customerEnquiry->customer->created_at)
                            <div class="mt-1">
                                <i class="ti ti-calendar-plus"></i>
                                {{ format_date($registeredAt) }}
                            </div>
                        @elseif($userType === 'sales' && $salesAssignedAt = $customerEnquiry->customer->scams->first()?->sales_assigned_at)
                            <div class="mt-1">
                                <i class="ti ti-calendar-plus"></i>
                                {{ format_date($salesAssignedAt) }}
                            </div>
                        @elseif($userType === 'drafting' && $draftingAssignedAt = $customerEnquiry->customer->scams->first()?->drafting_assigned_at)
                            <div class="mt-1">
                                <i class="ti ti-calendar-plus"></i>
                                {{ format_date($draftingAssignedAt) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 row">
                <div class="col-12">
                    <div class="fs-4 fw-bold mb-2">Source of enquiry
                    </div>
                    <div class="fs-3">
                        {{ $customerEnquiry->source?->title }}
                    </div>
                </div>
                <div class="col-lg-6 mt-4">
                    @if ($userType === 'admin' || $userType === 'sales')
                        <div class="my-3">
                            <h4 class="my-0">Enquiry Sales Status</h4>
                            <div>
                                {{ $customerEnquiry->salesStatus?->title ?? 'N/A' }}
                            </div>
                        </div>
                    @endif
                    @if ($userType === 'admin' || $userType === 'drafting')
                        <div class="my-3">
                            <h4 class="my-0">Enquiry Drafting Status</h4>
                            <div>
                                {{ $customerEnquiry->draftingStatus?->title ?? 'N/A' }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if ($customerEnquiry->message)
                <div class="col-md-6">
                    <div class="fs-4 fw-bold mb-2">Message
                    </div>
                    <div class="fs-3">
                        {{ $customerEnquiry->message }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@include('admin.customer-enquiries._status_update_data_modal', [
    'refreshOnUpdate' => true
])