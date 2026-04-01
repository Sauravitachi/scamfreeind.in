@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.users.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.customers.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.customers.store'),
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
