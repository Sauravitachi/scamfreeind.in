@php
    /** @var \App\Models\Role $role */
@endphp

@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.roles.show'),
    'buttons' => [
        auth()->user()->can(Permission::ROLE_UPDATE->value)
            ? ['label' => 'Edit Role', 'icon' => 'ti ti-edit', 'url' => route('admin.roles.edit', $role)]
            : null,
    ],
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>
                        <span class="text-secondary"><i class="ti ti-award"></i> Role :</span>
                        <span class="fw-bold">{{ $role->name }}</span>
                    </h2>
                    @if ($role->permissions->isNotEmpty())
                        <h3 class="form-label mb-0">
                            Permissions Granted
                        </h3>

                        <div class="row">
                            @foreach ($role->permissions as $permission)
                                <div class="col-lg-3 col-md-4 col-6 mt-3">
                                    <i class="ti ti-user-check text-primary h3 me-1"></i>
                                    <span class="h4">
                                        {{ $permission->label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="mt-3 text-danger">
                            0 Permissions granted
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
