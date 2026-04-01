<?php

namespace App\Models;

use App\Enums\ScamStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamStatusUnassignRecord extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    public const  UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scam_id',
        'assignee_id',
        'status_id',
        'enquiry_status_id',
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

    protected static function booted(): void
    {
        static::created(function (ScamStatusUnassignRecord $record) {

            $statusType = $record->status_type->value ?? $record->status_type;

            // updating record in scams table
            $record->scam->updateQuietly([
                "latest_{$statusType}_status_unassign_record_id" => $record->id,
            ]);

        });
    }

    /**
     * Get the related scam of this record
     */
    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }

    /**
     * Get the assignee of the scam from this record
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the status of the scam from this record
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_id');
    }

    /**
     * Get the enquiry status fo the scam from this record
     */
    public function enquiryStatus(): BelongsTo
    {
        return $this->belongsTo(CustomerEnquiryStatus::class, 'enquiry_status_id');
    }
}
