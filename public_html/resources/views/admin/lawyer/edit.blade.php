@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.lawyer.edit'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.lawyer.form', [
                        'method' => 'PUT',
                        'actionUrl' => route('admin.lawyer.update', $lawyer),
                        'scamTypes' => $scamTypes,
                        'scamLead' => $lawyer,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
