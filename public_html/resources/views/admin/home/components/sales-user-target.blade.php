@use(\App\Constants\Permission)

@php /** @var \App\DTO\DashboardStats $stat */ @endphp


<div class="row mb-4 gy-4">
    <!-- Sales Targets -->
    <div class="col-xl-6">
        <div class="card shadow-sm border-0 h-100" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pt-4 px-4">
                <h3 class="card-title fw-bold text-primary mb-0">
                    <i class="ti ti-chart-arrows mr-2"></i> Sales Performance & Targets
                </h3>
            </div>
            <div class="card-body px-4">
                @php $salesRank = 1; @endphp
                @forelse($salesUsers as $user)
                    @if($user->getAttribute('current_target'))
                        <div class="mb-4 position-relative">
                            @if($salesRank === 1)
                                <div class="position-absolute" style="top: -15px; right: 0; z-index: 10;">
                                    <!-- <span class="badge bg-warning text-dark border shadow-sm px-2 py-1" style="border-radius: 20px; font-size: 0.7rem;">
                                        <i class="ti ti-crown"></i> TOP PERFORMER
                                    </span> -->
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">
                                    <span class="text-muted small">#{{ $salesRank++ }}</span> {{ $user->name }}
                                </span>
                                @can(Permission::DASHBOARD_USER_STATS)
                                <span class="badge bg-soft-primary text-primary px-2 py-1">
                                    {{ format_amount($user->actual_sales) }} / {{ format_amount($user->current_target->target_amount) }}
                                </span>
                                @endcan
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px; background-color: #f0f2f5;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                     role="progressbar" 
                                     style="width: {{ $user->progress_percent }}%; border-radius: 5px;" 
                                     aria-valuenow="{{ $user->progress_percent }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ $user->progress_percent }}% Completed</small>
                                <!-- <small class="fw-bold text-success">{{ $user->current_target->ends_at->diffForHumans(now(), true) }} left</small> -->
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-4">
                        <i class="ti ti-target-off text-muted display-6 mb-2"></i>
                        <p class="text-muted">No active sales targets found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Drafting Targets -->
    <div class="col-xl-6">
        <div class="card shadow-sm border-0 h-100" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pt-4 px-4">
                <h3 class="card-title fw-bold text-warning mb-0">
                    <i class="ti ti-pencil-check mr-2"></i> Drafting Performance & Targets
                </h3>
            </div>
            <div class="card-body px-4">
                @php $draftingRank = 1; @endphp
                @forelse($draftingUsers as $user)
                    @if($user->getAttribute('current_target'))
                        <div class="mb-4 position-relative">
                            @if($draftingRank === 1)
                                <div class="position-absolute" style="top: -15px; right: 0; z-index: 10;">
                                    <span class="badge bg-warning text-dark border shadow-sm px-2 py-1" style="border-radius: 20px; font-size: 0.7rem;">
                                        <i class="ti ti-crown"></i> TOP PERFORMER
                                    </span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">
                                    <span class="text-muted small">#{{ $draftingRank++ }}</span> {{ $user->name }}
                                </span>
                                <span class="badge bg-soft-warning text-warning px-2 py-1">
                                    {{ format_amount($user->actual_sales) }} / {{ format_amount($user->current_target->target_amount) }}
                                </span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px; background-color: #f0f2f5;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                     role="progressbar" 
                                     style="width: {{ $user->progress_percent }}%; border-radius: 5px;" 
                                     aria-valuenow="{{ $user->progress_percent }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ $user->progress_percent }}% Completed</small>
                                <small class="fw-bold text-info">{{ $user->current_target->ends_at->diffForHumans(now(), true) }} left</small>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-4">
                        <i class="ti ti-target-off text-muted display-6 mb-2"></i>
                        <p class="text-muted">No active drafting targets found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: #e0f2ff; }
    .bg-soft-warning { background-color: #fff8e1; }
    .card:hover { transform: translateY(-3px); transition: all 0.3s ease; }
</style>
