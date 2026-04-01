@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.users.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.users.form', [
                        'method' => 'PUT',
                        'actionUrl' => route('admin.users.update', $user),
                        'user' => $user,
                        'roles' => $roles,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
