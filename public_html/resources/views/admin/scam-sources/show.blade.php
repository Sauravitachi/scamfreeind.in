@php
    /** @var \App\Models\ScamSource $scamSource */
@endphp

@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-sources.show'),
    'buttons' => [
        auth()->user()->can(Permission::SCAM_SOURCE_UPDATE->value)
            ? ['label' => 'Edit Scam Source', 'icon' => 'ti ti-edit', 'url' => route('admin.scam-sources.edit', $scamSource)]
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
                            <div class="fs-4 fw-bold mb-2">Source Slug
                            </div>
                            <div class="fs-3">{{ $scamSource->slug }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Source Title
                            </div>
                            <div class="fs-3">{{ $scamSource->title }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fs-4 fw-bold mb-2">Indicator Color
                            </div>
                            <div class="fs-3">
                                @if ($scamSource->indicator_color)
                                    <div class="cursor-pointer" style="width: 80px; height: 30px; background-color: {{ $scamSource->indicator_color }};" title="{{ $scamSource->indicator_color }}"></div>
                                @else
                                    <div class="text-secondary">
                                        N/A
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
