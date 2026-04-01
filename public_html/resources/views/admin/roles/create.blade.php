@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.roles.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.roles.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.roles.store'),
                        'permissions' => $permissions,
                        'roles' => $roles,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
