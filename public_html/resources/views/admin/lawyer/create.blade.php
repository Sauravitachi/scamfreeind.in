@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.lawyer.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.lawyer.form', [
                        'method' => 'POST',
                        'actionUrl' => route('admin.lawyer.store'),
                        'scamTypes' => $scamTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
