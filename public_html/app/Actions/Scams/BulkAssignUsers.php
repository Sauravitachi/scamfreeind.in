<?php

namespace App\Actions\Scams;

use App\Constants\Permission;
use App\Http\Requests\Admin\BulkAssignUserToScamRequest;
use App\Models\CustomerEnquiry;
use App\Models\Scam;
use App\Notifications\CaseAssignedNotification;
use App\Services\ScamService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BulkAssignUsers
{
    public function __construct(
        protected ScamService $scamService
    ) {}

    public function handle(BulkAssignUserToScamRequest $request): bool
    {
        return DB::transaction(function () use ($request): bool {

            $user = $request->user();
            $data = $request->validated();

            $types = ['sales', 'drafting', 'service', 'sub_admin'];

            $update = [];

            $scams = Scam::with(['salesStatus:id,is_lock', 'draftingStatus:id,is_lock'])->whereIn('id', $data['scams'])->get();
            $customerEnquiries = isset($data['customer_enquiries']) && ! empty($data['customer_enquiries']) ? CustomerEnquiry::whereIn('id', $data['customer_enquiries'])->get(['id']) : null;

            foreach ($types as $type) {
                $permissionConst = strtoupper($type) . '_MANAGEMENT';
                $permission = constant(Permission::class . '::' . $permissionConst);

                // same reasoning as in ScamService: sales managers should
                // be able to bulk-assign sub-admins even if they lack the
                // SUB_ADMIN_MANAGEMENT permission.
                $canAssign = $user->can($permission->value);
                if (! $canAssign && $type === 'sub_admin') {
                    $canAssign = $user->can(Permission::SALES_MANAGEMENT->value);
                }

                if ($canAssign) {
                    $column = $type === 'sub_admin' ? 'sub_admin_id' : "{$type}_assignee_id";

                    if (($data[$column] ?? null) !== null) {
                        $update[$column] = ($data[$column] == 0) ? null : $data[$column];
                    }

                    if (in_array($type, ['sales', 'drafting'])) {
                        if (($data["{$type}_status_id"] ?? null) !== null) {
                            $update["{$type}_status_id"] = ($data["{$type}_status_id"] == 0) ? null : $data["{$type}_status_id"];
                        }
                    }
                }
            }
            if (($data['source_id'] ?? null) !== null) {
                $update['scam_source_id'] = ($data['source_id'] == 0) ? null : $data['source_id'];
            }

            if (! empty($update)) {
                $scams->each(function (Scam $scam, int $index) use ($user, $update, $customerEnquiries): void {

                    $scamUpdate = $update;

                    // locked status permission check
                    if ($scam->salesStatus?->is_lock && $user->cannot(Permission::UPDATE_LOCKED_SALES_STATUS)) {
                        unset($scamUpdate['sales_status_id']);
                    }
                    if ($scam->draftingStatus?->is_lock && $user->cannot(Permission::UPDATE_LOCKED_DRAFTING_STATUS)) {
                        unset($scamUpdate['drafting_status_id']);
                    }

                    if (array_key_exists('sub_admin_id', $scamUpdate) && $scamUpdate['sub_admin_id'] !== null) {

                        $scamUpdate['sales_assignee_id'] = null;
                        $scamUpdate['sales_status_id'] = null;
                    }

                    $scam->fill($scamUpdate);

                    if (
                        $customerEnquiries?->isNotEmpty() &&
                        isset($customerEnquiries[$index]) &&
                        (
                            $scam->isDirty('sales_assignee_id') ||
                            $scam->isDirty('drafting_assignee_id')
                        )
                    ) {
                        $customerEnquiries[$index]->update(['manually_assigned_at' => now()]);
                    }

                    $this->scamService->logScamActivityBeforeUpdate($scam);
                    $scam->save();

                    if ($scam->salesAssignee && $scam->wasChanged('sales_assignee_id') && $scam->sales_assignee_id) {
                        Notification::sendNow($scam->salesAssignee, new CaseAssignedNotification($scam));
                    }
                    if ($scam->draftingAssignee && $scam->wasChanged('drafting_assignee_id') && $scam->drafting_assignee_id) {
                        Notification::sendNow($scam->draftingAssignee, new CaseAssignedNotification($scam));
                    }

                });

                return true;
            }

            return false;
        });
    }
}
