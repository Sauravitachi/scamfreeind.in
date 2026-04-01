@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-leads.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-leads.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.scam-leads.store'),
                        'scamTypes' => $scamTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
