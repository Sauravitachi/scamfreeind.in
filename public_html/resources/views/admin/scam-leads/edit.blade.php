@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scam-leads.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('admin.scam-leads.form', [
                        'method' => 'PUT',
                        'actionUrl' => route('admin.scam-leads.update', $scamLead),
                        'scamTypes' => $scamTypes,
                        'scamLead' => $scamLead,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
