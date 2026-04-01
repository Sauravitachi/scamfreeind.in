@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.customer-enquiry-statuses.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.customer-enquiry-statuses.form', [
                        'actionUrl' => route('admin.customer-enquiry-statuses.store'),
                        'method' => 'POST'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
