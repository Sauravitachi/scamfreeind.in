@use(App\Constants\Permission)

@php /** @var \App\Models\Scam $scam */ @endphp

@php
    $__file_preview_dropdown_menu = [
        [
            'title' => 'Download',
            'icon' => 'ti ti-download',
            'onclick' => 'ScamDetailModule.scamFileDownload(event);',
            'permit' => true
        ],
        [
            'title' => 'Delete',
            'icon' => 'ti ti-trash',
            'onclick' => 'ScamDetailModule.scamFileDelete(event);',
            'permit' => auth()->user()->can(Permission::DELETE_SCAM_STATUS_FILE)
        ],
    ];
@endphp

<div class="card mb-3">
    <div class="card-header">
        @php($isFirstTab = true)
        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
            @can(Permission::VIEW_SCAM_CUSTOM_UPLOADED_FILES)
                <li class="nav-item" role="presentation">
                    <a href="#custom-uploaded-media-tab" class="nav-link scam-status-file-nav {{ $isFirstTab ? 'active' : '' }}" data-value="1" data-bs-toggle="tab" aria-selected="true" role="tab">
                        <i class="fs-3 me-1 ti ti-file"></i>
                        Custom Uploaded
                        @if ($scamFilesCount = $scam->scamFiles->count())
                            <span class="badge bg-blue text-blue-fg ms-2">
                                {{ $scamFilesCount }}
                            </span>
                        @endif
                    </a>
                </li>
                @php($isFirstTab = false)
            @endcan
            @can(Permission::VIEW_SCAM_STATUS_UPLOADED_FILES)
                <li class="nav-item" role="presentation">
                    <a href="#status-uploaded-media-tab" class="nav-link scam-status-file-nav {{ $isFirstTab ? 'active' : '' }}" data-value="2" data-bs-toggle="tab" aria-selected="true" role="tab">
                        <i class="fs-3 me-1 ti ti-file"></i>
                        Status Uploaded
                        @if ($statusFilesCount = $scam->scamStatusFiles->count())
                            <span class="badge bg-blue text-blue-fg ms-2">
                                {{ $statusFilesCount }}
                            </span>
                        @endif
                    </a>
                </li>
                @php($isFirstTab = false)
            @endcan
        </ul>
    </div>
    <div class="card-body">
        @php($isFirstTab = true)
        <div class="tab-content">
            @can(Permission::VIEW_SCAM_CUSTOM_UPLOADED_FILES)
                <div class="tab-pane scam-status-file-tab-pane {{ $isFirstTab ? 'active show' : '' }}" data-value="1" id="custom-uploaded-media-tab" role="tabpanel">
                    <div class="scam-files-card">
                        @if ($scam->scamFiles->isNotEmpty())
                            <div class="row g-3">
                                @foreach ($scam->scamFiles as $scamFile)
                                    <div class="col-lg-4">
                                        <div class="card card-link" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px  0px"
                                            data-id="{{ $scamFile->id }}"
                                            data-type="scam_file"
                                            data-file-url="{{ $scamFile->file->url }}"
                                            data-file-name="{{ $scamFile->file->original_name ?? '' }}">
                                            @if ($scamFile->file->isPreviewableFile)
                                                <div class="scam-file-preview text-end"
                                                    style="background: url('{{ $scamFile->file->url }}');">
                                                    <x-admin.dropdown button-class='bg-secondary opacity-70'
                                                        :menu="$__file_preview_dropdown_menu" />
                                                </div>
                                            @else
                                                <div class="bg-white scam-file-preview text-end"
                                                    style="background: url({{ asset('assets/theme/img/icons/file-icon.svg') }}); background-size: auto;">
                                                    <x-admin.dropdown button-class='bg-secondary opacity-70'
                                                        :menu="$__file_preview_dropdown_menu" />
                                                </div>
                                            @endif
        
                                            <div class="card-body pb-2">
                                                <div class="h4 card-title text-center mb-1 text-truncate-1">
                                                    {{ $scamFile->message ?? $scamFile->file->original_name }}
                                                </div>
                                                <div class="h5 mt-3">
                                                    <i class="ti ti-cloud-up"></i> Uploaded by
                                                    <span class="text-primary">
                                                        {{ $scamFile->user->name }}
                                                    </span>
                                                    <div>
                                                        at {{ format_date($scamFile->created_at) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <h4 class="text-secondary">
                                No media uploaded!
                            </h4>
                        @endif
                    </div>
                </div>
                @php($isFirstTab = false)
            @endcan
            @can(Permission::VIEW_SCAM_STATUS_UPLOADED_FILES)
                <div class="tab-pane scam-status-file-tab-pane {{ $isFirstTab ? 'active show' : '' }}" data-value="2" id="status-uploaded-media-tab" role="tabpanel">
                    <div class="scam-files-card">
                        @if ($scam->scamStatusFiles->isNotEmpty())
                            <div class="row g-3">
                                @foreach ($scam->scamStatusFiles as $statusFile)
                                    <div class="col-lg-4">
                                        <div class="card card-link" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px  0px"
                                            data-id="{{ $statusFile->id }}"
                                            data-type="scam_status_file"
                                            data-file-url="{{ $statusFile->file->url }}"
                                            data-file-name="{{ $statusFile->file->original_name ?? '' }}">
                                            @if ($statusFile->file->isPreviewableFile)
                                                <div class="scam-file-preview text-end"
                                                    style="background: url('{{ $statusFile->file->url }}');"
                                                    onclick="redirect('{{ $statusFile->file->url }}', true);"
                                                    role="button"
                                                    >
                                                    <x-admin.dropdown button-class='bg-secondary opacity-70'
                                                        :menu="$__file_preview_dropdown_menu" />
                                                </div>
                                            @else
                                                <div class="bg-white scam-file-preview text-end"
                                                    style="background: url({{ asset('assets/theme/img/icons/file-icon.svg') }}); background-size: auto;"
                                                    onclick="redirect('{{ $statusFile->file->url }}', true);"
                                                    role="button"
                                                    >
                                                    <x-admin.dropdown button-class='bg-secondary opacity-70'
                                                        :menu="$__file_preview_dropdown_menu" />
                                                </div>
                                            @endif
        
                                            <div class="card-body pb-2">
                                                <div class="h4 card-title mb-1 text-truncate-1">
                                                    Status : {{ $statusFile->status->title }}
                                                </div>
                                                <div class="h5 mt-3">
                                                    <i class="ti ti-cloud-up"></i> Uploaded by
                                                    <span class="text-primary">
                                                        {{ $statusFile->file->user->name }}
                                                    </span>
                                                    <div>
                                                        at {{ format_date($statusFile->file->created_at) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <h4 class="text-secondary">
                                No media uploaded!
                            </h4>
                        @endif
                    </div>
                </div>
                @php($isFirstTab = false)
            @endcan
        </div>
    </div>
</div>