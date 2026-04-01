@use(App\Enums\ScamStatusType)
@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@php /** @var \App\Enums\ScamStatusType $type */ @endphp

@php $canUpdate = auth()->user()->can(Permission::SCAM_STATUS_TRANSITION_UPDATE); @endphp

@extends('admin.layouts.app', [
    'pageTitle' => $type->label() . ' Status Transition',
    'breadcrumbs' => Breadcrumbs::render('admin.scam-statuses.transition')
])

@include('admin.layouts.components.select2')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{  route('admin.scam-statuses.handle-transition', $type)  }}" method="POST" id="status-transition-form">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="row gy-3">
                            @foreach ($statuses as $status)
                                <div class="col-lg-4">
                                    <x-admin.select 
                                        name='transition[{{ $status->id }}][]' 
                                        class='transition-select select2' 
                                        label='{{ "$loop->iteration. $status->title" }}' 
                                        :options="$statuses->where('id', '!=', $status->id)->pluck('title', 'id')->toArray()" 
                                        data-status-id="{{ $status->id }}" 
                                        data-default="{{ $status->nextStatuses->pluck('id')->implode(',') }}" 
                                        multiple 
                                    />
                                </div>
                            @endforeach
                        </div>
                        @if ($canUpdate)
                            <div class="text-end mt-3">
                                <x-admin.button label='Save Changes' submit />
                            </div> 
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {

            @if (!$canUpdate)
                disable_form('#status-transition-form');
            @endif

            ajaxForm('#status-transition-form', {
                handleToast: true
            });

            $('.transition-select').each(function() {
                var $select = $(this);
                var defaultValue = $select.data('default');
                
                if (defaultValue) {
                    var values = defaultValue.toString().split(',');
                    $select.val(values).trigger('change');
                }
            });
        });
    </script>
@endpush