@use(App\Constants\Permission)
@use(Diglactic\Breadcrumbs\Breadcrumbs)

@extends('admin.layouts.app', [
    'pageTitle' => 'App UI Data Manager',
    'breadcrumbs' => Breadcrumbs::render('admin.app-ui-data.index'),
    'buttons' => [
        auth()->user()->can(Permission::APP_UI_DATA_CREATE->value)
            ? ['label' => 'Add New Section', 'icon' => 'ti ti-plus', 'url' => route('admin.app-ui-data.create')]
            : null,
    ],
])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row row-cards">
            @foreach($data as $item)
                <div class="col-md-4 col-xl-3 mb-3">
                    <div class="card card-stacked shadow-sm hover-shadow transition-all">
                        @if($item->name === 'video_section')
                            <div class="ribbon bg-red">Special</div>
                        @endif
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <span class="avatar avatar-xl avatar-rounded bg-primary-lt">
                                    <i class="ti ti-{{ $item->name === 'video_section' ? 'video' : ($item->name === 'hero_section' ? 'star' : 'settings') }} fs-1"></i>
                                </span>
                            </div>
                            <h3 class="mb-1 text-capitalize">{{ str_replace(['_', '-'], ' ', $item->name) }}</h3>
                            <div class="text-muted small mb-3">
                                @if($item->updated_at)
                                    Modified {{ $item->updated_at->diffForHumans() }}
                                @else
                                    Not configured yet
                                @endif
                            </div>
                            
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.app-ui-data.edit', ['app_ui_datum' => $item->name]) }}" class="btn btn-primary d-flex align-items-center">
                                    <i class="ti ti-adjustments me-1"></i> Manage
                                </a>
                                
                                @if(auth()->user()->can(Permission::APP_UI_DATA_DELETE->value) && $item->id)
                                    <button class="btn btn-icon btn-outline-danger delete-btn" data-id="{{ $item->id }}" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($data->isEmpty())
                <div class="col-12">
                    <div class="card p-5 text-center bg-light">
                        <div class="text-muted mb-3"><i class="ti ti-database-off fs-1"></i></div>
                        <h2>No UI data found</h2>
                        <p class="text-secondary">Start by adding a new UI section to manage your app content.</p>
                        <a href="{{ route('admin.app-ui-data.create') }}" class="btn btn-primary mt-2">
                            <i class="ti ti-plus"></i> Create first section
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('.delete-btn').on('click', function() {
                const id = $(this).data('id');
                const url = @js(route('admin.app-ui-data.destroy', ['app_ui_datum' => ':id'])).replace(':id', id);
                
                Popup.askConfirmation({
                    variant: 'danger',
                    message: 'Are you sure you want to delete this UI data section? This will remove the configuration permanently.',
                    onConfirm: async function() {
                        await runAjax({
                            url: url,
                            method: 'DELETE',
                            handleToast: true,
                            success: function() {
                                location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
    </style>
@endpush