<div class="card mt-3">
    <div class="card-body">
        <h3 class="card-title mb-3">
            Customer Cases
        </h3>
        <div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            @if ($userType === 'sales' || $userType === 'admin')
                                <th>Sales Assignee</th>
                            @endif
                            @if ($userType === 'drafting' || $userType === 'admin')
                                <th>Drafting Assignee</th>
                            @endif
                            <th>Scam Type</th>
                            <th>Scam Amount</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customerEnquiry->customer->scams as $scam)
                            <tr>
                                <td class="text-secondary">
                                   #{{ $loop->iteration }}
                                </td>
                                @if ($userType === 'sales' || $userType === 'admin')
                                    <th>
                                        {{ $scam->salesAssignee?->name_with_username ?? 'N/A' }}
                                    </th>
                                @endif
                                @if ($userType === 'drafting' || $userType === 'admin')
                                    <th>
                                        {{ $scam->draftingAssignee?->name_with_username ?? 'N/A' }}
                                    </th>
                                @endif
                                <td>
                                    {{ $scam->scamType?->title ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $scam->scam_amount ? format_amount($scam->scam_amount) : 'N/A' }}
                                </td>
                                <td class="text-secondary">
                                    @if($userType === 'sales' && $scam->sales_assigned_at)
                                        {{ format_date($scam->sales_assigned_at) }}
                                    @elseif($userType === 'drafting' && $scam->drafting_assigned_at)
                                        {{ format_date($scam->drafting_assigned_at) }}
                                    @elseif($scam->created_at)
                                        {{ format_date($scam->created_at) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="100">
                                    No cases found!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>