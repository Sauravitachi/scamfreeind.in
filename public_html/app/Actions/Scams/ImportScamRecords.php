<?php

namespace App\Actions\Scams;

use App\Enums\ScamActivityEvent;
use App\Enums\ScamAssigneeType;
use App\Enums\ScamStatusType;
use App\Models\Customer;
use App\Models\ScamSource;
use App\Models\ScamStatus;
use App\Models\ScamType;
use App\Models\User;
use App\Utilities\Memory;
use App\Utilities\Structure;
use Illuminate\Support\Facades\DB;

class ImportScamRecords
{
    public function handle(array $data): void
    {
        DB::transaction(function () use ($data): void {

            $scamTypes = ScamType::all(['id', 'slug', 'title']);

            $salesUsers = User::whereSales()->get(['id', 'username', 'name']);
            $draftingUsers = User::whereDrafting()->get(['id', 'username', 'name']);
            $serviceUsers = User::whereService()->get(['id', 'username', 'name']);

            $salesStatuses = ScamStatus::where('type', ScamStatusType::SALES->value)
                ->where('is_file_required', false)->get(['id', 'slug', 'title']);

            $draftingStatuses = ScamStatus::where('type', ScamStatusType::DRAFTING->value)
                ->where('is_file_required', false)->get(['id', 'slug', 'title']);

            foreach ($data as $record) {
                // check if already exists with phone_number
                $customer = Customer::where('phone_number', $record['phone_number'])->first();

                $splitName = Structure::splitFullName($record['name'] ?? '');

                if (! $customer) {
                    $customer = Customer::create([
                        'first_name' => $splitName->firstName,
                        'last_name' => $splitName->lastName,
                        'phone_number' => $record['phone_number'],
                        'email' => trim_or_null($record['email'] ?? null),
                        'country_code' => trim_or_null($record['country_code'] ?? null),
                    ]);
                }

                $scamTypeValue = trim_or_null($record['scam_type']);

                $scamData['scam_amount'] = trim_or_null($record['scam_amount']);
                $scamData['scam_type_id'] = $scamTypes->first(fn (ScamType $type): bool => $type->id == $scamTypeValue || $type->slug == $scamTypeValue)?->id ?? null;

                $assignments = [
                    'sales_assignee_id' => ['value' => trim_or_null($record['sales_assignee']), 'users' => $salesUsers],
                    'drafting_assignee_id' => ['value' => trim_or_null($record['drafting_assignee']), 'users' => $draftingUsers],
                    'service_assignee_id' => ['value' => trim_or_null($record['service_assignee']), 'users' => $serviceUsers],
                ];

                foreach ($assignments as $key => $assignment) {
                    $scamData[$key] = null;
                    if ($assignment['value']) {
                        if ($assignee = $assignment['users']->first(fn (User $user) => $user->id == $assignment['value'] || $user->username == $assignment['value'])) {
                            $scamData[$key] = $assignee->id;
                        }
                    }
                }

                $statuses = [
                    'sales_status_id' => ['value' => trim_or_null($record['sales_status']), 'statuses' => $salesStatuses],
                    'drafting_status_id' => ['value' => trim_or_null($record['drafting_status']), 'statuses' => $draftingStatuses],
                ];

                foreach ($statuses as $key => $status) {
                    $scamData[$key] = null;
                    if ($status['value']) {
                        if ($statusObj = $status['statuses']->first(fn ($stat) => $stat->id == $status['value'] || $stat->slug == $status['value'])) {
                            $scamData[$key] = $statusObj->id;
                        }
                    }
                }

                $scam = $customer->scams()->create([
                    ...$scamData,
                    'scam_source_id' => Memory::remember('sheet_import_source_id', fn () => ScamSource::sheetImportSource(['id'])?->id),
                ]);

                // assignee logs
                foreach (ScamAssigneeType::cases() as $assigneeType) {
                    if (isset($scamData["{$assigneeType->value}_assignee_id"]) && ($scamAssigneeId = $scamData["{$assigneeType->value}_assignee_id"])) {
                        $_assignee = Memory::remember(
                            "__scam_import_assignee_id:$scamAssigneeId",
                            fn (): ?User => User::whereSales()->where('id', $scamAssigneeId)->first(['id', 'name', 'username'])
                        );
                        if ($_assignee) {
                            $scam->logActivity("Assigned to {$assigneeType->value} member : {$_assignee->name_with_username}", ScamActivityEvent::{strtoupper("{$assigneeType->value}_assign")});
                        }
                    }
                }

                // status logs
                foreach (ScamStatusType::cases() as $statusType) {
                    if (isset($scamData["{$statusType->value}_status_id"]) && ($scamStatusId = $scamData["{$statusType->value}_status_id"])) {
                        $_status = Memory::remember(
                            "__scam_import_status_id:$scamStatusId",
                            fn (): ?ScamStatus => ScamStatus::where('type', $statusType->value)->where('id', $scamStatusId)->first(['id', 'title'])
                        );
                        if ($_status) {
                            $scam->logActivity(ucfirst($statusType->value)." status updated : {$_status->title}", ScamActivityEvent::{strtoupper("{$statusType->value}_status")});
                        }
                    }
                }
            }

        });
    }
}
