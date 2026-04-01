@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-statuses.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-statuses.form', [
                        'actionUrl' => route('admin.scam-statuses.update', $scamStatus),
                        'method' => 'PUT',
                        'scamStatusTypes' => $scamStatusTypes,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
