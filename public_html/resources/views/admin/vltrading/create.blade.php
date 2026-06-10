@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.vltrading.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.vltrading.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.vltrading.store'),
                        'scamTypes' => $scamTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
