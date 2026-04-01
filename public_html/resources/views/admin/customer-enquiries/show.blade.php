@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@php
    $user = auth()->user();
    $userType = $user->userType();
    $isUserDrafting = $user->getRoleString() === 'drafting';
@endphp

@extends('admin.layouts.app', [
    'pageTitle' => $isUserDrafting ? 'Escalation' : Breadcrumbs::current()->title,
    'breadcrumbs' => $isUserDrafting ? Breadcrumbs::render('admin.escalations.show') : Breadcrumbs::render('admin.customer-enquiries.show')
])

@include('admin.layouts.components.select2')

@section('content')
    <div class="row">
        <div class="col-lg-7">
            @include('admin.customer-enquiries._customer_details')
            @include('admin.customer-enquiries._customer_scams')
        </div>
        <div class="col-lg-5">
            <div class="card" style="max-height: 800px; overflow-y: scroll;">
                <div class="card-body">
                    <h3 class="title">
                        Status Tracking
                    </h3>
                    <div class="fs-3 lh">
                        @foreach ($allCustomerEnquiries as $enquiry)
                            @if ($enquiry->records->isNotEmpty())
                                <div class="mb-1">
                                    <i class="ti ti-arrow-down"></i>
                                    Enquiry at {{ format_date($enquiry->created_at) }}
                                </div>
                                <x-admin.alert variant="primary">
                                    @foreach ($enquiry->records as $record)
                                        <div class="mb-2">
                                            @if ( $record->status?->title)
                                                Changed to status : 
                                                <strong class="text-success">{{  $record->status?->title }}</strong>
                                            @else
                                                <span class="text-primary">Status Removed</span>
                                            @endif
                                            <br /> 
                                            <div class="ms-3">
                                                @if ($record->created_at)
                                                    at
                                                    <span class="text-secondary">{{ format_date($record->created_at) }}</span>
                                                @endif
                                                @if ($record->causer)
                                                    by
                                                    <span class="text-warning">{{ $record->causer->nameWithUsername }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </x-admin.alert>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

