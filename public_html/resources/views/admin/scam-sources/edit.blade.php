@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-sources.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-sources.form', [
                        'actionUrl' => route('admin.scam-sources.update', $scamSource),
                        'method' => 'PUT'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
