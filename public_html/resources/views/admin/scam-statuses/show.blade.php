@php
    /** @var \App\Models\ScamStatus $scamStatus */
@endphp

@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-statuses.show'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_STATUS_UPDATE->value)
            ? ['label' => 'Edit Scam Status', 'icon' => 'ti ti-edit', 'url' => route('admin.scam-statuses.edit', $scamStatus)]
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
                            <div class="fs-3">{{ $scamStatus->slug }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Status Title
                            </div>
                            <div class="fs-3">{{ $scamStatus->title }}</div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Status Type
                            </div>
                            <div class="fs-3">{{ $scamStatus->type->label() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Notify After Days
                            </div>
                            <div class="fs-3">{{ $scamStatus->notify_after_days ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
