@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@php /** @var \App\Models\WhatsappMessageLog $log */ @endphp

@extends('admin.layouts.app', [
    'pageTitle' => Breadcrumbs::current()->title,
    'breadcrumbs' => Breadcrumbs::render('admin.whatsapp-message-logs.show'),
])

@include('admin.layouts.components.highlighjs')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-between mb-5">
                       <div class="col-lg-6 text-start">
                           <div>
                                <h3>
                                    <span class="text-secondary">
                                        <i class="ti ti-user"></i>
                                        {{ $log->recipientEntityType }} :
                                    </span>
                                    {{ $log->recipient->getUserDetailText() }}
                                    <br />
                                    <span class="text-secondary">
                                        <i class="ti ti-brand-whatsapp"></i>
                                        Whatsapp Number :
                                    </span>
                                    {{ $log->whatsapp_number }}
                                    <br />
                                    <span class="text-secondary">
                                        <i class="ti ti-clock-hour-1"></i>
                                        Date/Time :
                                    </span>
                                    {{ format_date($log->created_at) }}
                                </h3>
                           </div>
                       </div>
                       <div class="col-lg-6 text-end">
                            <h3>
                                <span class="text-secondary">Template Name :</span> {{ $log->template_name }}
                                <br>
                                <span class="text-secondary">Broadcast Name :</span> {{ $log->broadcast_name }}
                                <br>
                            </h3>
                       </div>
                       <div class="col-12">
                            <h3 class="mb-2">Response Status</h3>
                            <h1>
                                @if ($log->response_status_code === 200)
                                    <span class="text-success">
                                        <i class="ti ti-progress-check"></i> Success
                                    </span>
                                @else
                                    <span class="text-danger">
                                        <i class="ti ti-circle-dashed-x"></i> Failed - {{ $log->response_status_code }}
                                    </span>
                                @endif
                            </h1>
                        </div>
                       <div class="col-12">
                            <h3 class="my-0">Request Payload</h3>
                            @if ($log->payload)
                               <x-admin.code-block language='json' :content="json_encode($log->payload, JSON_PRETTY_PRINT)" />
                            @else
                                <span class="text-secondary">
                                    N/A
                                </span>
                            @endif
                       </div>
                       <div class="col-12">
                            <h3 class="my-0">Response Json</h3>
                            @if ($log->response)
                                <x-admin.code-block language='json' :content="json_encode($log->response, JSON_PRETTY_PRINT)" />
                            @else
                                <span class="text-secondary">
                                    N/A
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>hljs.highlightAll();</script>
@endpush