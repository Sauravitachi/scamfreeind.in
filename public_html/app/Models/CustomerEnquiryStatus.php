<?php

namespace App\Models;

use App\Enums\CustomerEnquiryStatusType;
use App\Foundation\Model;

class CustomerEnquiryStatus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'type',
        'is_remark_required',
        'consider_resolved',
        'unassign_scam',
        'unassign_scam_in_days',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => CustomerEnquiryStatusType::class,
        'is_remark_required' => 'boolean',
        'consider_resolved' => 'boolean',
        'unassign_scam' => 'boolean',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted(): void
    {
        /**
         * Before saving the record (create/update)
         */
        static::saving(function (CustomerEnquiryStatus $status): void {
            self::handleSavingEvent($status);
        });

    }

    private static function handleSavingEvent(CustomerEnquiryStatus $status)
    {

        if ($status->isDirty('consider_resolved')) {

            if ($status->consider_resolved) {
                $q = CustomerEnquiryStatus::where('type', $status->type)->where('consider_resolved', true);
                if ($status->id) {
                    $q->whereNot('id', $status->id);
                }
                $q->update(['consider_resolved' => false]);
            } else {
                $status->consider_resolved = $status->getOriginal('consider_resolved');
            }

        }

        if ($status->isDirty('is_remark_required') && $status->is_remark_required) {

            if ($status->consider_resolved) {
                $status->is_remark_required = false;
            }
        }
    }
}
