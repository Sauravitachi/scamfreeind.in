<?php

namespace App\Actions\CustomerEnquiry;

use App\Enums\FreezeType;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryFreeze;
use App\Models\FreezeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Currently only working for drafting members
 */
class FreezeAllUsersEnquiry
{
    protected ?int $hoursToFreeze;

    protected ?int $freezeThreshold;

    protected ?string $officeStartTime;

    protected ?string $officeEndTime;

    public function __construct()
    {
        $this->hoursToFreeze = setting('hours_to_freeze_enquiries', null);
        $this->freezeThreshold = setting('freeze_enquiry_threshold', null);
        // $this->officeStartTime = setting('office_start_time', null);
        // $this->officeEndTime = setting('office_end_time', null);
    }

    public function handle()
    {
        DB::transaction(function () {

            if ($this->freezeThreshold === null) {
                return;
            }

            $users = User::whereDrafting()->get(['id']);

            foreach ($users as $user) {

                $query = CustomerEnquiry::whereDraftingAssignee($user->id)
                    ->where('occurrence', '>', 0)
                    ->where(function (Builder $q) {
                        $q->whereNull('drafting_status_id')
                            ->orWhereHas('draftingStatus', function (Builder $q2) {
                                $q2->where('consider_resolved', false);
                            });
                    })
                    ->where('created_at', '<=', now()->subHours($this->hoursToFreeze));

                $enquiryCount = $query->count();

                if ($enquiryCount >= $this->freezeThreshold) {

                    $freeze = CustomerEnquiryFreeze::updateOrCreate(
                        ['user_id' => $user->id],
                        ['status_type' => 'drafting']
                    );

                    if ($freeze->wasRecentlyCreated) {
                        FreezeLog::create([
                            'type' => FreezeType::ENQUIRY,
                            'freeze' => true,
                            'user_id' => $user->id,
                        ]);
                    }

                } else {

                    $delQuery = CustomerEnquiryFreeze::where('user_id', $user->id)->where('status_type', 'drafting');

                    if ($delQuery->exists()) {
                        $delQuery->delete();
                        FreezeLog::create(['type' => FreezeType::ENQUIRY, 'freeze' => false, 'user_id' => $user->id]);
                    }

                }

            }

        });
    }
}
