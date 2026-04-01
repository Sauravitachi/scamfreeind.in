@php
    /** @var \App\Models\CustomerEnquiryStatus $customerEnquiryStatus */
@endphp

@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.customer-enquiry-statuses.show'),
    'buttons' => [
        auth()->user()->can(Permission::CUSTOMER_ENQUIRY_STATUS_UPDATE->value)
            ? ['label' => 'Edit Scam Status', 'icon' => 'ti ti-edit', 'url' => route('admin.customer-enquiry-statuses.edit', $customerEnquiryStatus)]
            : null,
    ],
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Status Slug
                            </div>
                            <div class="fs-3">{{ $customerEnquiryStatus->slug }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Status Title
                            </div>
                            <div class="fs-3">{{ $customerEnquiryStatus->title }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
