@php /** @var \App\Models\Scam $scam */ @endphp

@use(App\Enums\ScamStatusReview)
@use(App\Constants\Permission)

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            {{ ucfirst($status_type->value) }} Status Review
        </div>
        <table class="table">
            <tr>
                <td class="text-start">Review Status</td>
                <td class="text-end">
                    <span class="rounded p-1" style="background-color: {{ $statusRecord->review_color }};">
                        {{ $statusRecord->review->label() }}
                    </span>
                </td>
            </tr>
            {{-- <tr>
                <td class="text-start">Scam Status</td>
                <td class="text-end">
                    <span class="rounded p-1">
                        {{ $status->title }}
                    </span>
                </td>
            </tr> --}}
            <tr>
                <td class="text-start">Status Changed By</td>
                <td class="text-end">
                    <span class="rounded p-1">
                        {{ $statusRecord->causer->name_with_username }} - 
                        <span class="text-secondary">{{ format_date($statusRecord->created_at) ?? '' }}</span>
                    </span>
                </td>
            </tr>
            @can(Permission::{'SCAM_' . strtoupper($status_type->value) . '_STATUS_REVIEW_UPDATE'})
                @if ($statusRecord->review === ScamStatusReview::PENDING)
                    <tr>
                        <td></td>
                        <td class="d-flex justify-content-end gap-2">
                            <x-admin.button label='Approve' variant='success' icon='ti ti-circle-check' onclick="handleScamReview({{ $scam->id }}, '{{ $status_type }}', 'approved')" />
                            <x-admin.button label='Reject' variant='danger' icon='ti ti-cancel' onclick="handleScamReview({{ $scam->id }}, '{{ $status_type }}', 'reject')" />
                        </td>
                    </tr>
                @endif
            @endcan
        </table>
    </div>
</div>