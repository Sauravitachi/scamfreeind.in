@php
    /** @var \App\Models\ScamRegistrationAmount $scamRegistrationAmount */
@endphp

@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-registration-amounts.show'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_REGISTRATION_AMOUNT_UPDATE->value)
            ? ['label' => 'Edit amount', 'icon' => 'ti ti-edit', 'url' => route('admin.scam-registration-amounts.edit', $scamRegistrationAmount)]
            : null,
    ],
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row gy-5 mb-5">
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Title</div>
                            <div class="fs-3">{{ $scamRegistrationAmount->title }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Amount</div>
                            <div class="fs-3">{{ format_amount($scamRegistrationAmount->amount) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Points</div>
                            <div class="fs-3">{{ $scamRegistrationAmount->points }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Is Active?</div>
                            <div class="fs-3">{{ $scamRegistrationAmount->is_active ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
