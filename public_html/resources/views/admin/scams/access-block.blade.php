@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.scams.index'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            <x-admin.alert variant='danger' icon='ti ti-alert-triangle' important>
                Cannot access cases right now as you enquiries/escaltions are freezed!
            </x-admin.alert>
        </div>
    </div>
@endsection