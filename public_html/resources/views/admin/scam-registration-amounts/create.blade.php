@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-registration-amounts.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-registration-amounts.form', [
                        'actionUrl' => route('admin.scam-registration-amounts.store'),
                        'method' => 'POST'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
