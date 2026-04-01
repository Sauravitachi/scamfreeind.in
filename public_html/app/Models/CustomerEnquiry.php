<?php

namespace App\Models;

use App\Foundation\Model;
use App\Services\CustomerEnquiryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerEnquiry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'message',
        'scam_source_id',
        'sales_status_id',
        'drafting_status_id',
        'sales_status_updated_at',
        'drafting_status_updated_at',
        'remark',
        'occurrence',
        'manually_assigned_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'drafting_status_updated_at' => 'datetime',
        'sales_status_updated_at' => 'datetime',
        'manually_assigned_at' => 'datetime',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::creating(function (CustomerEnquiry $customerEnquiry) {
            CustomerEnquiryService::getInstance()->handleNewEnquiyCreatingEvent($customerEnquiry);
        });

        static::updated(function (CustomerEnquiry $customerEnquiry) {
            CustomerEnquiryService::getInstance()->handleEnquiryUpdatedEvent($customerEnquiry);
            $customerEnquiry->saveQuietly();
        });
    }

    public function scopeWhereSalesAssignee(Builder $query, null|array|int $user_id = null, bool $exclude = false, bool $bypassed = false): void
    {
        $query->whereHas('customer.scams', function (Builder $q) use ($user_id, $exclude, $bypassed): void {
            $q->where('is_duplicate', false);
            if ($bypassed) {
                $q->whereHas('salesStatus', function (Builder $q2) {
                    $q2->where('bypass_enquiry', true);
                });
            } else {
                $q->where(function (Builder $q) {
                    $q->whereNull('sales_status_id')
                        ->orWhereHas('salesStatus', function (Builder $q2) {
                            $q2->where('bypass_enquiry', false);
                        });
                });
            }

            if ($user_id !== null) {
                if (is_array($user_id)) {
                    $q->{$exclude ? 'whereNotIn' : 'whereIn'}('sales_assignee_id', $user_id);
                } else {
                    $q->{$exclude ? 'whereNot' : 'where'}('sales_assignee_id', $user_id);
                }
                $q->whereNull('drafting_assignee_id');
            } else {
                $q->whereNull('drafting_assignee_id');
            }
        });
    }

    public function scopeWhereDraftingAssignee(Builder $query, null|array|int $user_id = null, bool $exclude = false, bool $bypassed = false): void
    {
        $query->whereHas('customer.scams', function (Builder $q) use ($user_id, $exclude, $bypassed): void {
            $q->where('is_duplicate', false);
            if ($bypassed) {
                $q->whereHas('draftingStatus', function (Builder $q2) {
                    $q2->where('bypass_enquiry', true);
                });
            } else {
                $q->where(function (Builder $q) {
                    $q->whereNull('drafting_status_id')
                        ->orWhereHas('draftingStatus', function (Builder $q2) {
                            $q2->where('bypass_enquiry', false);
                        });
                });
            }
            if ($user_id !== null) {
                if (is_array($user_id)) {
                    $q->{$exclude ? 'whereNotIn' : 'whereIn'}('drafting_assignee_id', $user_id);
                } else {
                    $q->{$exclude ? 'whereNot' : 'where'}('drafting_assignee_id', $user_id);
                }
            } else {
                $q->whereNotNull('drafting_assignee_id');
            }
        });
    }

    public static function createEnquiry(Customer $customer, ?ScamSource $scamSource = null, ?string $remark = null): CustomerEnquiry
    {
        $enquiry = CustomerEnquiry::create(['customer_id' => $customer->id, 'scam_source_id' => $scamSource?->id, 'remark' => $remark]);

        return $enquiry;
    }

    /**
     * Get the related customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the status record of this enquiry
     */
    public function records(): HasMany
    {
        return $this->hasMany(CustomerEnquiryStatusRecord::class);
    }

    /**
     * Get the source of enquiry
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(ScamSource::class, 'scam_source_id');
    }

    /**
     * Get the Customer Enquiry Sales Status
     */
    public function salesStatus(): BelongsTo
    {
        return $this->belongsTo(CustomerEnquiryStatus::class, 'sales_status_id');
    }

    /**
     * Get the Customer Enquiry Drafting Status
     */
    public function draftingStatus(): BelongsTo
    {
        return $this->belongsTo(CustomerEnquiryStatus::class, 'drafting_status_id');
    }
}
