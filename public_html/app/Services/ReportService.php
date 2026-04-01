<?php

namespace App\Services;

use App\DTO\AdminDailyCaseReport;
use App\Enums\ScamAssigneeType;
use App\Mail\Admin\DailyCaseReport;
use App\Models\Scam;
use App\Models\ScamLead;
use App\Models\ScamSource;
use App\Models\ScamStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;

class ReportService extends Service
{
    public function userCaseReport(Request $request): array
    {

        $assigneeType = ScamAssigneeType::tryFrom(
            $request->string('assignee_type', ScamAssigneeType::SALES->value)
        );

        abort_if(! $assigneeType, 400);

        $statusType = $assigneeType->statusType();

        $columns = [['name' => 'status', 'data' => 'status', 'title' => 'Status']];

        $users = User::{'where'.ucfirst($assigneeType->value)}()
            ->when(
                $request->filled('assignee_status'),
                fn (Builder $q) => $q->where('status', $request->boolean('assignee_status'))
            )
            ->get(['id', 'name']);

        $statuses = ScamStatus::where('type', $statusType)->get(['id', 'title'])
            ->prepend(new ScamStatus(['title' => 'N/A']));

        $columns = array_merge($columns, $users->map(fn (User $u) => ['name' => $u->id, 'data' => $u->id, 'title' => $u->name])->toArray());

        $rows = [];

        foreach ($statuses as $status) {

            $row['status'] = $status;

            foreach ($users as $user) {

                $query = Scam::where('is_duplicate', false)
                    ->where("{$statusType->value}_status_id", $status->id)
                    ->whereNotNull("{$assigneeType->value}_assignee_id")
                    ->where("{$assigneeType->value}_assignee_id", $user->id)
                    ->when(
                        $request->filled('assigned_at'),
                        function (Builder $q) use ($request, $assigneeType) {
                            $range = carbon_date_range($request->string('assigned_at'), 'to', expandDates: true);
                            $q->whereBetween("{$assigneeType->value}_assigned_at", [$range->start, $range->end]);
                        }
                    )
                    ->when(
                        $request->filled('status_updated_at'),
                        function (Builder $q) use ($request, $assigneeType) {
                            $range = carbon_date_range($request->string('status_updated_at'), 'to', expandDates: true);
                            $q->whereBetween("{$assigneeType->value}_status_updated_at", [$range->start, $range->end]);
                        }
                    );

                $row[$user->id] = $query->count();
            }

            $rows[] = $row;
        }

        return compact('assigneeType', 'columns', 'rows');
    }

    public function sendAdminDailyCaseReport(): ?SentMessage
    {
        $todayScams = Scam::whereToday()->count();
        $todayLeads = ScamLead::whereToday()->count();

        $sourceCases = ScamSource::all()->map(function (ScamSource $source): array {

            $caseCount = Scam::whereToday()->where('scam_source_id', $source->id)->count() +
                ScamLead::whereToday()->where('scam_source_id', $source->id)->count();

            return [
                'source' => $source->title,
                'case_count' => $caseCount,
            ];
        })->toArray();

        $totalCases = $todayScams + $todayLeads;

        return Mail::to('adb.tusharnain@gmail.com')->send(new DailyCaseReport(new AdminDailyCaseReport(
            totalCases: $totalCases,
            sourceCases: $sourceCases
        )));
    }
}
