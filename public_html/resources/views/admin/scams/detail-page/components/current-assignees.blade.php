@php /** @var \App\Models\Scam $scam */ @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="h2 card-title">
            Current Assignees
        </div>
        <div class="h3 mt-3">
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <td class="text-start">
                            Sales
                        </td>
                        <td class="text-end">
                            @if ($assignee = $scam->salesAssignee)
                                <span> {{ $assignee->name_with_username }}</span>
                                @if ($at = $scam->sales_assigned_at)
                                    <h4 class="text-secondary">
                                        at {{ format_date($at) }}
                                    </h4>
                                @endif
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-start">
                            Drafting
                        </td>
                        <td class="text-end">
                            @if ($assignee = $scam->draftingAssignee)
                                <span>{{ $assignee->name_with_username }}</span>
                                @if ($at = $scam->drafting_assigned_at)
                                    <h4 class="text-secondary">
                                        at {{ format_date($at) }}
                                    </h4>
                                @endif
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-start">
                            Service
                        </td>
                        <td class="text-end">
                            @if ($assignee = $scam->serviceAssignee)
                                <span>{{ $assignee->name_with_username }}</span>
                                @if ($at = $scam->service_assigned_at)
                                    <h4 class="text-secondary">
                                        at {{ format_date($at) }}
                                    </h4>
                                @endif
                            @else
                                <span class="text-secondary">N/A</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>