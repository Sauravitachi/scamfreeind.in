<?php

namespace App\Services;

use App\Constants\ActivityEvent;
use App\Constants\Permission;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\EloquentDataTable;

class UserActivityService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $request = request();

        $user = $request->user();

        $query = Activity::query();

        $query->with(['causer:id,name,username,avatar']);

        if (! $user->can(Permission::VIEW_ALL_USERS_ACTIVITIES) && $user->can(Permission::VIEW_SELF_USERS_ACTIVITIES)) {
            $query->causedBy(causer: $user);
        }

        if ($filterUserId = $request->input('filter_user_id')) {
            $query->where('causer_id', $filterUserId);
        }

        if ($filterEvent = $request->input('filter_event')) {
            $query->where('event', $filterEvent);
        }

        if ($filterIpAddress = $request->input('filter_ip_address')) {
            $query->where('ip_address', $filterIpAddress);
        }

        if ($filterCreatedAt = $request->input('filter_created_at')) {
            $range = carbon_date_range($filterCreatedAt, 'to', expandDates: true);
            $query->whereBetween('created_at', [$range->start, $range->end]);
        }

        $table = datatables()->eloquent($query);

        $table->addColumn('user', function (Activity $activity) {
            return $activity->causer->nameWithUsername;
        });

        $table->addColumn('profile_avatar', fn (Activity $a) => $a->causer->profileAvatar);

        $table->editColumn('description', fn (Activity $a) => ucwords($a->description));

        $table->editColumn('event', fn (Activity $a) => ActivityEvent::from($a->event)->label());

        $table->editColumn('created_at', fn (Activity $a): ?string => format_date($a->created_at));

        return $table;
    }
}
