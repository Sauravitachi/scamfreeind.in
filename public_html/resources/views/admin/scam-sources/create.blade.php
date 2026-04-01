@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-sources.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-sources.form', [
                        'actionUrl' => route('admin.scam-sources.store'),
                        'method' => 'POST'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
