@extends('admin.layouts.app', [
    'pageTitle' => 'Chat Window',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <livewire:chat-window />
                </div>
            </div>
        </div>
    </div>
@endsection
