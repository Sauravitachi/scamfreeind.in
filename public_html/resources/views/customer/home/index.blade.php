@use(App\Http\Requests\Customer\RaiseEnquiryRequest)

@extends('customer.layouts.app')

@push('style')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
@endpush

@php
    $mainScam = $scams->first();
@endphp

@section('content')
<div class="row main-page gy-2">
    <div class="col-12">
        <h2 class="text-primary fw-normal">
            Welcome
            <span class="fw-bold">
                  @php
                    $customer = \App\Models\Customer::find(session('customer_id'));
                @endphp
                {{ $customer?->full_name ?? 'Customer' }}
            </span>
    </div>
    <div class="col-lg-3 order-1 order-lg-2">
        <div class="card">
            <div class="card-body">
                @if($mainScam->draftingAssignee)
                    Your case manager information:
                    <br>
                    <div class="mt-3">
                        <h2>{{ $mainScam->draftingAssignee->name }}</h2>
                        <h3>
                            <i class="ti ti-phone"></i>
                            {{ $mainScam->draftingAssignee->fullPhoneNumber }}
                        </h3>
                    </div>
                @else
                    <div class="text-muted">
                        <span class="fs-3">Your case manager will be assigned shortly. Please check back later.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-9 order-2 order-lg-1">
        @foreach ($scams as $index => $scam)
        @php
        $itemId = "collapse-item-{$index}";
        $headingId = "heading-item-{$index}";
        $isAccordianOpened = $scams->count() === 1 && $index === 0;
        @endphp
        <div class="card mb-3">
            <div class="accordion accordion-flush" id="accordion-{{ $index }}">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="{{ $headingId }}">
                        <button class="accordion-button {{ $isAccordianOpened ? '' : 'collapsed' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $itemId }}"
                            aria-expanded="{{ $isAccordianOpened ? 'true' : 'false' }}"
                            aria-controls="{{ $itemId }}">
                            <div class="d-flex">
                                <div>
                                    Case <strong class="mx-1">#{{ $scam->track_id }}</strong>
                                    @if ($scam->scamType)
                                    (<strong class="mx-1">{{ $scam->scamType->title }}</strong>)
                                    @endif
                                </div>
                                <div class="ms-2">
                                    @if ($scam->draftingAssignee && $scam->draftingStatus)
                                    - <span class="ms-1">
                                        [ 
                                            <span class="fw-normal">Status updated to</span>
                                            <span class="text-decoration-underline">{{ $scam->draftingStatus->title }} </span>
                                            <span class="fw-normal">at {{ format_date($scam->drafting_status_updated_at) }} </span>
                                        ]
                                    </span>
                                    @else
                                    - <span class="ms-1">Drafting in progress</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="{{ $itemId }}"
                        class="accordion-collapse collapse {{ $isAccordianOpened ? 'show' : '' }}"
                        aria-labelledby="{{ $headingId }}">
                        <div class="accordion-body">
                            <ul class="timeline_ul">
                                @foreach ($scam->statusRecords as $statusRecord)
                                    @if($statusRecord->status)
                                        <li class="timeline_li">
                                            @if ($at = $statusRecord->created_at)
                                                <div class="timeline_time">
                                                    {{ format_date($at) }}
                                                </div>
                                            @endif
                                            <p class="timeline_p">
                                                {{ $statusRecord->status->title }}
                                            </p>
                                            <div class="stas-files-container">
                                                @php $statusFiles = $scam->scamStatusFiles->where('status_id', $statusRecord->status_id) @endphp

                                                <div class="d-flex gap-3">
                                                    @foreach ($statusFiles as $statusFile)
                                                        @if ($statusFile->file->isPreviewableFile)
                                                            <a href="{{ $statusFile->file->url }}" data-lightbox="status-gallery-{{ $statusRecord->id }}" data-title="{{ $statusRecord->status->title }}">
                                                                <img width="100" src="{{ $statusFile->file->url }}" alt="status-file" style="cursor: pointer;">
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="mt-5 text-end">
                                <x-admin.button label='Raise Enquiry' icon='ti ti-alert-triangle' class='btn-sm' variant='danger' onclick="RAISE_ENQUIRY_MODULE.open({{ $scam->id }});" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="modal modal-blur fade" id="raise-enquiry-modal" tabindex="-1" style="display: none;"
aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form class="modal-content" method="POST" action="{{ route('customer.home.raise-enquiry') }}" id="raise-enquiry-form">
            <div class="modal-header">
                <h5 class="modal-title">Raise Case Enquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <x-admin.button label="Submit" icon='ti ti-device-floppy' submit />
            </div>
        </form>
    </div>
</div>
<div id="raise-enquiry-form-template" style="display: none;">
    <div>
        <input type="hidden" name="scam_id" class="scam_id_inp">
        <x-admin.textarea label="Your Query" name='query' placeholder='write your query in less than 1000 characters.' required />
    </div>
</div>
@endsection

@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

{!! js_validation_custom_event(RaiseEnquiryRequest::class, 'form#raise-enquiry-form', '#raise-enquiry-modal', 'validate:enquiry-form') !!}

<script>
    lightbox.option({
        'fadeDuration': 100,
        'resizeDuration': 0,
        'imageFadeDuration': 0,
        'wrapAround': false
    });

    const RAISE_ENQUIRY_MODULE = {
        register: function() {
            this.$modal = $('#raise-enquiry-modal');
            this.formTemplate= $('#raise-enquiry-form-template').html();
        },
        open: function(scamId) {
            this.$modal.find('.modal-body').html(this.formTemplate);
            this.$modal.find('.scam_id_inp').val(scamId);
            this.$modal.modal('show');

            this.$modal.trigger('validate:enquiry-form');

            ajaxForm('#raise-enquiry-form', {
                handleToast: true,
                success: function (res) {
                    RAISE_ENQUIRY_MODULE.close();
                }
            });
        },
        close: function() {
            this.$modal.modal('hide');
        }
    };

    $(document).ready(function() {
        RAISE_ENQUIRY_MODULE.register();
    });

</script>
@endpush