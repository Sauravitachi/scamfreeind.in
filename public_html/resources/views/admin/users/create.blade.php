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
                    @include('admin.users.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.users.store'),
                        'roles' => $roles,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
