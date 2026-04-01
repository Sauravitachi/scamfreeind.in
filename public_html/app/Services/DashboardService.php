<?php

namespace App\Services;

use App\DTO\DashboardStats;
use App\Enums\ScamStatusType;
use App\Models\Customer;
use App\Models\Scam;
use App\Models\ScamStatus;
use App\Models\User;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DashboardService extends Service
{
    public function viewData(): array
    {

        $recentScams = Scam::with([
            'customer:id,first_name,last_name,country_code,dial_code,phone_number',
            'scamType:id,title',
            'salesAssignee:id,username,name',
            'draftingAssignee:id,username,name',
        ])
            ->whereNotDuplicate()
            ->limit(5)
            ->latest()->get();

        $getUsersWithTargets = function ($scopeQuery) {
            return $scopeQuery->where('status', true)
                ->with('salesTargets')
                ->get()
                ->map(function ($user) {
                    $target = $user->currentTarget;
                    if ($target) {
                        $actual = $user->getActualSalesAmount($target->starts_at, $target->ends_at);
                        $rawPercent = $target->target_amount > 0 ? round(($actual / $target->target_amount) * 100, 2) : 0;
                        
                        $user->setAttribute('current_target', $target);
                        $user->setAttribute('actual_sales', $actual);
                        $user->setAttribute('progress_percent', min(100, $rawPercent));
                        $user->setAttribute('raw_progress_percent', $rawPercent);
                    } else {
                        $user->setAttribute('raw_progress_percent', -1); // Users without targets go to bottom
                    }
                    return $user;
                })->sortByDesc('raw_progress_percent');
        };

        return [

            'stat' => $this->stat(),

            'salesUsers' => $getUsersWithTargets(User::whereSales()),

            'draftingUsers' => $getUsersWithTargets(User::whereDrafting()),

            'recentScams' => $recentScams,

        ];
    }

    public function ajax(Request $request)
    {
        $requestQuery = $request->input('query');

        return match ($requestQuery) {
            'sales-status-scam-count' => $this->getScamCountByStatuses($request, ScamStatusType::SALES),
            'drafting-status-scam-count' => $this->getScamCountByStatuses($request, ScamStatusType::DRAFTING),
            'customers-by-region' => $this->customersByRegion(),
            'scams-by-source' => $this->scamsBySource(),
            'case-chart' => $this->scamCountChart($request),
            default => null
        };
    }

    private function stat(): DashboardStats
    {
        $today = today();

        $totalUsers = User::count('id');
        $totalActiveUsers = User::where('status', true)->count('id');
        $loggedInUsers = User::whereLoggedIn()->count('id');
        $salesUsers = User::whereHas('roles', fn ($q) => $q->where('id', config('settings.sales_role_id')))->where('status', true)->count('id');
        $draftingUsers = User::whereHas('roles', fn ($q) => $q->where('id', config('settings.drafting_role_id')))->where('status', true)->count('id');
        $serviceUsers = User::whereHas('roles', fn ($q) => $q->where('id', config('settings.service_role_id')))->where('status', true)->count('id');

        $totalScams = Scam::whereNotDuplicate()->count();
        $todaysScams = Scam::whereNotDuplicate()->whereDate('created_at', $today)->count();

        $totalSalesAssignedScams = Scam::whereNotDuplicate()->whereNotNull('sales_assignee_id')->count();
        $todaysSalesAssignedScams = Scam::whereNotDuplicate()->whereNotNull('sales_assignee_id')->whereDate('sales_assigned_at', $today)->count();

        $totalDraftingAssignedScams = Scam::whereNotDuplicate()->whereNotNull('drafting_assignee_id')->count();
        $todaysDraftingAssignedScams = Scam::whereNotDuplicate()->whereNotNull('drafting_assignee_id')->whereDate('drafting_assigned_at', $today)->count();

        return new DashboardStats(
            totalUsers: $totalUsers,
            totalActiveUsers: $totalActiveUsers,
            totalLoggedInUsers: $loggedInUsers,
            totalSalesUsers: $salesUsers,
            totalDraftingUsers: $draftingUsers,
            totalServiceUsers: $serviceUsers,
            totalScams: $totalScams,
            todaysScams: $todaysScams,
            totalSalesAssignedScams: $totalSalesAssignedScams,
            todaysSalesAssignedScams: $todaysSalesAssignedScams,
            totalDraftingAssignedScams: $totalDraftingAssignedScams,
            todaysDraftingAssignedScams: $todaysDraftingAssignedScams
        );
    }

    private function getScamCountByStatuses(Request $request, ScamStatusType $type)
    {
        $lastXDays = $request->input('last_x_days');
        $userId = $request->input('user_id');

        $statuses = ScamStatus::where('type', $type)->get(['id', 'title'])
            ->prepend(new ScamStatus(['title' => 'N/A']));

        $statuses->each(function (ScamStatus $status) use ($type, $lastXDays, $userId): void {

            $query = Scam::whereNotDuplicate();

            $query->where("{$type->value}_status_id", $status->id);

            if ($userId) {
                $query->where("{$type->value}_assignee_id", $userId);
            } else {
                $validUserIds = User::{'where'.ucfirst($type->value)}()->pluck('id');
                $query->whereIn("{$type->value}_assignee_id", $validUserIds);
            }

            if (is_numeric($lastXDays)) {

                if ($lastXDays == 0) {
                    $query->whereDate("{$type->value}_assigned_at", today());
                } else {
                    $query->whereBetween("{$type->value}_assigned_at", [
                        now()->startOfDay()->subDays($lastXDays),
                        now(),
                    ]);
                }

            }

            $status->setAttribute('scam_count', $query->count());

        });

        // $nulledStatusScams = Scam

        return $statuses;
    }

    private function customersByRegion()
    {
        return Customer::select('country_code', DB::raw('count(*) as total'))
            ->groupBy('country_code')
            ->pluck('total', 'country_code');
    }

    private function scamsBySource()
    {
        return DB::table('scams')->select(
            'scam_sources.id',
            'scam_sources.slug',
            'scam_sources.title',
            'scam_sources.indicator_color',
            DB::raw('count(*) as count')
        )
            ->where('scams.is_duplicate', false)
            ->join('scam_sources', 'scams.scam_source_id', '=', 'scam_sources.id')
            ->groupBy(
                'scam_sources.id',
                'scam_sources.slug',
                'scam_sources.title',
                'scam_sources.indicator_color'
            )
            ->get();
    }

    private function scamCountChart(Request $request)
    {

        $lastXDays = $request->integer('last_x_days', 10);

        throw_if($lastXDays < 0, InvalidArgumentException::class);

        $startDate = today()->subDays($lastXDays - 1);
        $endDate = today();

        $query = Scam::whereNotDuplicate()->whereDate('created_at', '>=', $startDate);

        $count = $query->count('id');

        // Get the raw DB results
        $scamStatsRaw = $query
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Convert to map: date => count
        $scamStatsMap = $scamStatsRaw->keyBy('date');

        // Fill in all days with count (default 0 if missing)
        $result = collect();
        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->copy()->addDay());

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $result->push((object) [
                'date' => $formattedDate,
                'count' => $scamStatsMap[$formattedDate]->count ?? 0,
            ]);
        }

        return [
            'count' => $count,
            'chart_data' => $result,
        ];
    }
}
