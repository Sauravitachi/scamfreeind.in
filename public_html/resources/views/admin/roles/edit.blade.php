@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.roles.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.roles.form', [
                        'method' => 'PUT',
                        'actionUrl' => route('admin.roles.update', $role),
                        'role' => $role,
                        'roles' => $roles,
                        'permissions' => $permissions,
                        'rolePermissions' => $rolePermissions,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
