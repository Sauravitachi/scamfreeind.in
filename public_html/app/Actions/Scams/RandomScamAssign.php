<?php

namespace App\Actions\Scams;

use App\Http\Requests\Admin\RandomScamAssignRequest;
use App\Models\Scam;
use App\Models\User;
use App\Notifications\CaseAssignedNotification;
use App\Services\ResponseService;
use App\Services\ScamService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RandomScamAssign
{
    public function __construct(
        protected ScamService $scamService,
        protected ResponseService $responseService
    ) {}

    public function handle(RandomScamAssignRequest $request): void
    {
        DB::transaction(fn () => $this->assignScams($request));
    }

    private function assignScams(RandomScamAssignRequest $request): void
    {
        // Step 1: Get all selected sales assignees who are active
        $assignees = $this->getAssignees($request);
        $countPerAssignee = $request->integer('count', 0);
        $assigneeCount = $assignees->count();

        // Step 2: Build the filtered query based on current request filters
        // - Status filters (e.g., "Hold", "Pending")
        // - Amount ranges (lower bound, upper bound)
        // - Other application filters
        $query = $this->buildQuery($request);

        $availableCount = $query->count();
        $totalRequested = $countPerAssignee * $assigneeCount;

        // Step 3: VALIDATE that enough cases exist for equal distribution
        $this->validateSufficientCases($availableCount, $totalRequested);

        // Step 4: Assign cases to each selected assignee explicitly
        // This ensures each selected user receives exactly the requested count
        $this->assignEqualCases($query, $assignees, $countPerAssignee, $request);
    }

    private function buildQuery(RandomScamAssignRequest $request): Builder
    {
        return $this->scamService->getRequestTableQuery($request)
            ->when($request->filled('scam_amount_lb'), function (Builder $q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('scam_amount', '>=', $request->integer('scam_amount_lb'));
                    if ($request->boolean('include_null_amount')) {
                        $query->orWhereNull('scam_amount');
                    }
                });
            })
            ->when($request->filled('scam_amount_ub'), function (Builder $q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('scam_amount', '<=', $request->integer('scam_amount_ub'));
                    if ($request->boolean('include_null_amount')) {
                        $query->orWhereNull('scam_amount');
                    }
                });
            });
    }

    private function validateSufficientCases(int $availableCount, int $totalRequested): void
    {
        if ($availableCount === 0) {
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        'No cases are available with the current filters. Please adjust the filter values.',
                    ],
                ])
            );
        }

        if ($availableCount < $totalRequested) {
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        "Not enough cases available for equal distribution. You need {$totalRequested} cases but only {$availableCount} are available. " .
                        "Please select fewer assignees or reduce the count per assignee.",
                    ],
                ])
            );
        }
    }

    private function getAssigneeLoads($assignees, RandomScamAssignRequest $request): array
    {
        $assigneeIds = $assignees->pluck('id')->all();

        if (empty($assigneeIds)) {
            return [];
        }

        // IMPORTANT: buildQuery() includes ALL filters from the request
        // This means if a status filter is applied, only scams with that status are counted
        // Example: If filtering by "Hold" status, only holds are counted for each user's load
        $query = $this->buildQuery($request);

        $loads = (clone $query)
            ->whereIn('sales_assignee_id', $assigneeIds)
            ->select('sales_assignee_id', DB::raw('count(*) as total'))
            ->groupBy('sales_assignee_id')
            ->pluck('total', 'sales_assignee_id')
            ->toArray();

        return array_map('intval', $loads);
    }

    private function getAssignees(RandomScamAssignRequest $request)
    {
        return User::whereSales()
            ->where('status', true)
            ->whereIn('id', $request->validated('assignees', []))
            ->get()
            ->shuffle()
            ->values();
    }

    private function assignEqualCases(Builder $query, $assignees, int $countPerAssignee, RandomScamAssignRequest $request): void
    {
        $assigneeLoads = $this->getAssigneeLoads($assignees, $request);

        $assignees = $assignees->sortBy(fn (User $user) => $assigneeLoads[$user->id] ?? 0)->values();

        $selectedScamIds = [];
        $assignments = [];

        foreach ($assignees as $assignee) {
            $candidateQuery = (clone $query)
                ->where(function (Builder $q) use ($assignee) {
                    $q->whereNull('sales_assignee_id')
                      ->orWhere('sales_assignee_id', '<>', $assignee->id);
                });

            if (! empty($selectedScamIds)) {
                $candidateQuery->whereNotIn('scams.id', $selectedScamIds);
            }

            $scamIds = $candidateQuery->inRandomOrder()->limit($countPerAssignee)->pluck('id')->all();

            if (count($scamIds) < $countPerAssignee) {
                throw new HttpResponseException(
                    $this->responseService->errors([
                        'count' => [
                            'Not enough assignable cases are available for the selected users. Please reduce the count per assignee or adjust the filters.',
                        ],
                    ])
                );
            }

            foreach ($scamIds as $scamId) {
                $selectedScamIds[] = $scamId;
                $assignments[$scamId] = $assignee;
            }
        }

        $scams = Scam::whereIn('id', array_keys($assignments))->get()->keyBy('id');

        foreach ($assignments as $scamId => $assignee) {
            $this->assignScamToUser($scams[$scamId], $assignee);
        }
    }

    private function assignScamToUser(Scam $scam, User $user): void
    {
        $scam->fill(['sales_assignee_id' => $user->id]);

        if ($scam->isDirty('sales_assignee_id')) {
            $scam->sales_assigned_at = now();
            $this->scamService->logScamActivityBeforeUpdate($scam);
            $scam->update();

            if($user) {
                Notification::sendNow($user, new CaseAssignedNotification($scam));
            }
        }
    }
}
