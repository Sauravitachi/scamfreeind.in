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
                        'method' => 'PUT',
                        'actionUrl' => route('admin.customers.update', $customer),
                        'customer' => $customer,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
