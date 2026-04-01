@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-types.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.escalations.form', [
                        'form_url' => route('admin.escalations.store'),
                        'form_method' => 'POST',
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
