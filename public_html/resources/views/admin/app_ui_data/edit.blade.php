@extends('admin.layouts.app')
@section('content')
<!--begin::Post-->
<div class="post d-flex flex-column-fluid" id="kt_post">
    <!--begin::Container-->
    <div id="kt_content_container" class="container-fluid">
        @if($errors->any())
        <div class="alert alert-danger">
            {!! implode('', $errors->all('
            <h6>:message</h6>
            ')) !!}
        </div>
        @endif
        <!--begin::Form-->
        <form
            action="{{ route('admin.app-ui-data.update', $appUiData->name) }}"
            method="POST"
            class="form main-form"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')


        @include('admin.app_ui_data.forms.' . $appUiData->name)

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Save
            </button>
        </div>

        
        </form>
        <!--end::Form-->
        <!--end::Careers - Apply-->
    </div>
    <!--end::Container-->
</div>
<!--end::Post-->
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
{!! $validator !!}
@endpush