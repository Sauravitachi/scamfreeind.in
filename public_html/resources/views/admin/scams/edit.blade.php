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
                        'method' => 'PUT',
                        'actionUrl' => route('admin.scams.update', $scam),
                        'scam' => $scam,
                        'scamTypes' => $scamTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
