@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.vltrading.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.vltrading.form', [
                        'method' => 'PUT',
                        'actionUrl' => route('admin.vltrading.update', $vltrading),
                        'scamTypes' => $scamTypes,
                        'scamLead' => $vltrading,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
