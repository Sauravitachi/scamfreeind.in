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
        $assignees = $this->getAssignees($request);
        $countPerAssignee = $request->integer('count', 0);
        $assigneeCount = $assignees->count();

        $totalRequested = $countPerAssignee * $assigneeCount;

        $query = $this->buildQuery($request);

        $availableCount = $query->count();

        $this->validateRequestedCount($request, $countPerAssignee, $availableCount, $assigneeCount);

        $scams = $query->inRandomOrder()->limit($totalRequested)->get();

        $this->distributeScams($scams, $assignees);
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

    /**
     * Validate requested count where $inputCount is count per assignee,
     * $dbCount is total available scams,
     * $assigneeCount is number of assignees
     */
    private function validateRequestedCount(RandomScamAssignRequest $request, int $inputCount, int $dbCount, int $assigneeCount): void
    {
        $requiredTotal = $inputCount * $assigneeCount;

        $hasScamAmountFilter = $request->anyFilled('scam_amount_lb', 'scam_amount_ub');

        if ($hasScamAmountFilter) {
            $this->validateFilteredCount($request, $dbCount, $requiredTotal, $assigneeCount);
        } else {
            $this->validateGeneralCount($dbCount, $requiredTotal, $assigneeCount);
        }
    }

    private function validateFilteredCount(RandomScamAssignRequest $request, int $dbCount, int $requiredTotal, int $assigneeCount): void
    {
        if ($dbCount === 0) {
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        'No cases are available with the current assign filters. Please adjust the filter values to include more cases.',
                    ],
                ])
            );
        }

        $minCountPerAssignee = (int) floor($dbCount / max(1, $assigneeCount));

        if ($dbCount < $requiredTotal) {
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        "There are only {$dbCount} cases available with the current assign filter. ".
                        "Please provide count per assignee less than or equal to {$minCountPerAssignee}.",
                    ],
                ])
            );
        }
    }

    private function validateGeneralCount(int $dbCount, int $requiredTotal, int $assigneeCount): void
    {
        if ($dbCount === 0) {
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        'No cases are available with the current assign filters. Please adjust the filter values to include more cases.',
                    ],
                ])
            );
        }

        if ($dbCount < $requiredTotal) {
            $maxAssignablePerAssignee = (int) floor($dbCount / $assigneeCount);
            throw new HttpResponseException(
                $this->responseService->errors([
                    'count' => [
                        "The count must be less than or equal to {$maxAssignablePerAssignee} per assignee. ".
                        'Please reduce the count accordingly.',
                    ],
                ])
            );
        }
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

    private function distributeScams($scams, $assignees): void
    {
        $totalAssignees = $assignees->count();
        $index = 0;

        foreach ($scams as $scam) {
            $this->assignScamToUser($scam, $assignees[$index]);
            $index = ($index + 1) % $totalAssignees;
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
