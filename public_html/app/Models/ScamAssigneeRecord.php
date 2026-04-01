<?php

namespace App\Models;

use App\Enums\ScamAssigneeType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamAssigneeRecord extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scam_id',
        'assignee_id',
        'assignee_type',
        'unassign_status_id',
        'causer_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'assignee_type' => ScamAssigneeType::class,
    ];

    /**
     * Log the record
     */
    public static function logRecord(Scam $scam, ScamAssigneeType $type, User|int|null $causer, null|int|ScamStatus $unassignStatus = null): ScamAssigneeRecord
    {
        $causerId = $causer ? (is_int($causer) ? $causer : $causer->id) : null;
        $unassignStatusId = $unassignStatus ? (is_int($unassignStatus) ? $unassignStatus : $unassignStatus->id) : null;

        $column = $type === ScamAssigneeType::SUB_ADMIN ? 'sub_admin_id' : "{$type->value}_assignee_id";

        return ScamAssigneeRecord::create([
            'scam_id' => $scam->id,
            'assignee_id' => $scam->{$column},
            'assignee_type' => $type,
            'unassign_status_id' => $unassignStatusId,
            'causer_id' => $causerId,
        ]);
    }

    /**
     * Get the scam
     */
    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }

    /**
     * Get the assignee of the scam
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the causer
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Get the status which caused the unassignement of the scam
     */
    public function usassignStatus(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class, 'unassign_status_id');
    }
}
