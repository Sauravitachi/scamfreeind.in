@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.customer-enquiry-statuses.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.customer-enquiry-statuses.form', [
                        'actionUrl' => route('admin.customer-enquiry-statuses.update', $customerEnquiryStatus),
                        'method' => 'PUT'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
