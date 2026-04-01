<?php

namespace App\Models;

use App\Enums\FreezeType;
use App\Enums\ScamStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScamStatusFreeze extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status_id',
        'status_type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status_type' => ScamStatusType::class,
    ];

    public static function scopeWhereUser(Builder $query, User $user, string $statusType)
    {
        $query->where('user_id', $user->id)->where('status_type', $statusType);
    }

    public static function checkAndTryFreezeRelease(Scam $scam, ?int $statusId, ScamStatusType $scamStatusType)
    {
        $assigneeColumn = "{$scamStatusType->value}_assignee_id";

        $userId = $scam->$assigneeColumn;
        $user = User::where('id', $userId)->first();

        $freezeQuery = UserScamStatusFreeze::with('status:id,is_freezable,freeze_release_scams_threshold')
            ->where('user_id', $userId)
            ->where('status_type', $scamStatusType);

        if ($statusId) {
            $freezeQuery->where('status_id', $statusId);
        } else {
            $freezeQuery->whereNull('status_id');
        }

        $freeze = $freezeQuery->first();

        if ($freeze) {

            $status = $freeze->status;

            if ($status?->is_freezable && ($status->freeze_release_scams_threshold !== null)) {

                $statusCount = Scam::where($assigneeColumn, $userId)->where('is_duplicate', false)->where($scamStatusType->value.'_status_id', $statusId)
                    ->whereStatusFreezed($user, $freeze)->count();

                if ($statusCount <= $status->freeze_release_scams_threshold) {

                    // Release
                    $freeze->delete();

                    FreezeLog::create(['type' => FreezeType::SCAMS, 'freeze' => false, 'user_id' => $freeze->user_id, 'status_id' => $status?->id]);

                }

            }

            if (! $status) {

                // $freezeThreshold = setting("freeze_{$scamStatusType}_null_threshold", null);
                $releaseThreshold = setting("freeze_{$scamStatusType->value}_null_release_threshold", null);

                if ($releaseThreshold !== null) {

                    $statusCount = Scam::where($assigneeColumn, $userId)->where('is_duplicate', false)->whereNull($scamStatusType->value.'_status_id')->whereStatusFreezed($user, $freeze)->count();

                    if ($statusCount <= $releaseThreshold) {

                        // Release
                        $freeze->delete();

                        FreezeLog::create(['type' => FreezeType::SCAMS, 'freeze' => false, 'user_id' => $freeze->user_id, 'status_id' => null]);

                    }

                }

            }

        }
    }

    /**
     * Get the user of the freeze
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the freeze
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class);
    }
}
