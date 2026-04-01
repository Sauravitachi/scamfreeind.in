@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scams.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scams.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.scams.store'),
                        'scamTypes' => $scamTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
