@use(App\Constants\Permission)
@use(App\Enums\ScamStatusType)

@php /** @var \App\Models\Scam $scam */@endphp

@php
    $userRole = auth()->user()->getRoleString();
@endphp

@php
    $showSecondColumn = auth()->user()->canany(Permission::VIEW_SCAM_ASSIGNEE_LIST, Permission::VIEW_SCAM_LIFECYCLE);
@endphp

<div class="row">
    <div class="col-12">
        <div class="text-end mb-3">
            <x-admin.button label='Upload Files' icon='ti ti-upload' variant='primary'
                onclick="ScamFileUploadModule.open();" />
        </div>
    </div>
    <div class="{{ $showSecondColumn ? 'col-lg-6' : 'col-12' }}">

        @include('admin.scams.detail-page.components.scam-details')

        @if ($scam->customer_description)
            @include('admin.scams.detail-page.components.scam-customer-description')
        @endif
        
        @include('admin.scams.detail-page.components.customer-details')

        @canany([Permission::VIEW_SCAM_CUSTOM_UPLOADED_FILES, Permission::VIEW_SCAM_STATUS_UPLOADED_FILES])
            @include('admin.scams.detail-page.components.scam-media')
        @endcanany


        @if(auth()->user()->can(Permission::SALES_MANAGEMENT->value))
            @include('admin.scams.detail-page.components.status-lifecycle', [
                'statusType' => ScamStatusType::SALES,
                'causer' => null
            ])
        @endif
        @if(auth()->user()->can(Permission::DRAFTING_MANAGEMENT->value))
            @include('admin.scams.detail-page.components.status-lifecycle', [
                'statusType' => ScamStatusType::DRAFTING,
                'causer' => null
            ])
        @endif

    </div>
    @canany([Permission::VIEW_SCAM_ASSIGNEE_LIST, Permission::VIEW_SCAM_LIFECYCLE, Permission::SCAM_SALES_STATUS_REVIEW_SHOW, Permission::SCAM_DRAFTING_STATUS_REVIEW_SHOW])
        <div class="col-lg-6">
            @can(Permission::VIEW_SCAM_ASSIGNEE_LIST)
                @include('admin.scams.detail-page.components.current-assignees')
            @endcan
            @can(Permission::SCAM_SALES_STATUS_REVIEW_SHOW)
                @if ($scam->salesStatusRecord?->review)
                @include('admin.scams.detail-page.components.status-record', [
                    'status_type' => ScamStatusType::SALES,
                    'status' => $scam->salesStatus,
                    'statusRecord' => $scam->salesStatusRecord
                ])
                @endif
            @endcan
            @can(Permission::SCAM_DRAFTING_STATUS_REVIEW_SHOW)
                @if ($scam->draftingStatusRecord?->review)
                    @include('admin.scams.detail-page.components.status-record', [
                        'status_type' => ScamStatusType::DRAFTING,
                        'status' => $scam->draftingStatus,
                        'statusRecord' => $scam->draftingStatusRecord
                    ])
                @endif
            @endcan
            @can(Permission::VIEW_RECYCLED_SCAM_LIFECYCLE)
                @include('admin.scams.detail-page.components.recycled-lifecycle')
            @elsecan(Permission::VIEW_SCAM_LIFECYCLE)
                @include('admin.scams.detail-page.components.lifecycle')
            @endcan
        </div>
    @endcanany
</div>
