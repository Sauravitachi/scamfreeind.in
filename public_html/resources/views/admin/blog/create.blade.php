@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.blog.create'),
])

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.blog.form', ['action' => route('admin.blog.store')])
        </div>
    </div>
@endsection
