@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.states.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.states.form', [
                        'actionUrl' => route('admin.states.store'),
                        'method' => 'POST'
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
