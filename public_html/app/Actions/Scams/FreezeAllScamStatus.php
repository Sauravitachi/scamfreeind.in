<?php

namespace App\Actions\Scams;

use App\Enums\FreezeType;
use App\Enums\ScamStatusType;
use App\Models\FreezeLog;
use App\Models\Scam;
use App\Models\ScamStatus;
use App\Models\User;
use App\Models\UserScamStatusFreeze;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FreezeAllScamStatus
{
    protected ?int $freezeSalesNullThreshold;

    protected ?int $hoursToFreezeSalesNull;

    protected ?int $freezeDraftingNullThreshold;

    protected ?int $hoursToFreezeDraftingNull;

    public function __construct()
    {
        $this->freezeSalesNullThreshold = setting('freeze_sales_null_threshold', null);
        $this->hoursToFreezeSalesNull = setting('hours_to_freeze_sales_null', null);
        $this->freezeDraftingNullThreshold = setting('freeze_drafting_null_threshold', null);
        $this->hoursToFreezeDraftingNull = setting('hours_to_freeze_drafting_null', null);
    }

    public function handle(): void
    {
        DB::transaction(fn () => $this->freezeStatusesForEligibleUsers());
    }

    protected function freezeStatusesForEligibleUsers(): void
    {
        $users = $this->getEligibleUsers();

        foreach ($users as $user) {

            $userRole = $user->getRoleString();

            if (! $userRole) {
                continue;
            }

            $scamStatusType = ScamStatusType::tryFrom($userRole);

            if (! $scamStatusType) {
                continue;
            }

            $statuses = $this->getFreezableStatuses($scamStatusType);

            foreach ($statuses as $status) {
                $this->processStatusFreezing($user, $userRole, $status);
            }

            $this->processNullStatusFreezing($user, $userRole, $scamStatusType);
        }
    }

    protected function getEligibleUsers(): Collection
    {
        return User::whereHas('roles', function (Builder $q) {
            $q->whereIn('id', [
                config('settings.sales_role_id'),
                config('settings.drafting_role_id'),
            ]);
        })->get();
    }

    protected function getFreezableStatuses(ScamStatusType $type): Collection
    {
        return ScamStatus::where('type', $type)
            ->where('is_freezable', true)
            ->where('freeze_scams_threshold', '>=', 1)
            ->where('hours_to_freeze', '>=', 1)
            ->get(['id', 'title', 'type', 'is_freezable', 'hours_to_freeze', 'freeze_scams_threshold']);
    }

    protected function processStatusFreezing(User $user, string $userRole, ScamStatus $status): void
    {
        $count = Scam::where("{$userRole}_assignee_id", $user->id)
            ->where("{$userRole}_status_id", $status->id)
            ->where('is_duplicate', false)
            ->where("{$userRole}_status_updated_at", '<=', now()->subHours($status->hours_to_freeze))
            ->count();

        if ($count >= $status->freeze_scams_threshold) {
            $this->freezeStatus($user, $status);
        }
    }

    protected function processNullStatusFreezing(User $user, string $userRole, ScamStatusType $scamStatusType): void
    {
        if ($userRole === 'drafting') {
            $threshold = $this->freezeDraftingNullThreshold;
            $hours = $this->hoursToFreezeDraftingNull;
        } elseif ($userRole === 'sales') {
            $threshold = $this->freezeSalesNullThreshold;
            $hours = $this->hoursToFreezeSalesNull;
        } else {
            return;
        }

        if ($threshold === null || $hours === null) {
            return;
        }

        $count = Scam::where("{$userRole}_assignee_id", $user->id)
            ->whereNull("{$userRole}_status_id")
            ->where("{$userRole}_assigned_at", '<=', now()->subHours($hours))
            ->count();

        if ($count >= $threshold) {
            $this->freezeStatus($user, null, $scamStatusType);
        }
    }

    protected function freezeStatus(User $user, ?ScamStatus $status, ?ScamStatusType $overrideType = null): void
    {
        $statusId = $status?->id;
        $statusType = $overrideType ?? $status->type;

        $freeze = UserScamStatusFreeze::updateOrCreate(
            ['user_id' => $user->id, 'status_id' => $statusId],
            ['status_type' => $statusType]
        );

        if ($freeze->wasRecentlyCreated) {
            FreezeLog::create([
                'type' => FreezeType::SCAMS,
                'freeze' => true,
                'user_id' => $user->id,
                'status_id' => $statusId,
            ]);
        }
    }

    protected function unfreezeStatus(User $user, ?int $statusId): void
    {
        $query = UserScamStatusFreeze::where('user_id', $user->id)->where('status_id', $statusId);

        if (! $query->exists()) {
            return;
        }

        $query->delete();

        FreezeLog::create([
            'type' => FreezeType::SCAMS,
            'freeze' => false,
            'user_id' => $user->id,
            'status_id' => $statusId,
        ]);
    }
}
