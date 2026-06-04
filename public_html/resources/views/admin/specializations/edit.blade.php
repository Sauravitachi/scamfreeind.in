@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.specializations.edit', $specialization),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.specializations.form')
                </div>
            </div>
        </div>
    </div>
@endsection
