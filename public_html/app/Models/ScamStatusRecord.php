<?php

namespace App\Models;

use App\Constants\Status;
use App\Enums\ScamStatusReview;
use App\Enums\ScamStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamStatusRecord extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'scam_id',
        'status_id',
        'status_type',
        'status_remark',
        'status_notify_at',
        'status_notification_acknowledged_at',
        'causer_id',
        'review',
        'review_resolver_id',
        'review_resolved_at',
        'review_resolve_remark',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status_type' => ScamStatusType::class,
        'review' => ScamStatusReview::class,
        'status_notify_at' => 'datetime',
        'status_notification_acknowledged_at' => 'datetime',
        'review_resolved_at' => 'datetime',
    ];

    /**
     * Log the record
     */
    public static function logRecord(Scam $scam, ScamStatusType $type, User|int $causer): ScamStatusRecord
    {
        $status = $scam->{"{$type->value}Status"};

        if (request()->boolean('request:scam_status_rejected')) {

            $scamStatusRecord = $scam->{"{$type->value}StatusRecord"};

            if ($scamStatusRecord) {
                $scamStatusRecord->update(['review' => ScamStatusReview::REJECTED]);
            }

            return $scamStatusRecord;
        }

        return ScamStatusRecord::create([
            'scam_id' => $scam->id,
            'status_id' => $scam->{"{$type->value}_status_id"},
            'status_type' => $type,
            'causer_id' => is_int($causer) ? $causer : $causer->id,
            'review' => $status?->is_approval_required ? ScamStatusReview::PENDING : null,
        ]);
    }

    public function getReviewColorAttribute(): ?string
    {
        return Status::tryFrom($this->review?->value ?? '')?->color();
    }

    public function getReviewColorFadedAttribute(): ?string
    {
        return Status::tryFrom($this->review?->value ?? '')?->fadedColor();
    }

    /**
     * Get the scam of the record
     */
    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }

    /**
     * Get the status of the file
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class, 'status_id');
    }

    /**
     * Get the causer of the record
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Get the review resolver of the record
     */
    public function reviewResolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_resolver_id');
    }
}
