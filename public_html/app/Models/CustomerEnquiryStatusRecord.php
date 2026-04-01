<?php

namespace App\Models;

use App\Enums\CustomerEnquiryStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEnquiryStatusRecord extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_enquiry_id',
        'status_id',
        'status_type',
        'remark',
        'causer_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status_type' => CustomerEnquiryStatusType::class,
    ];

    /**
     * Log the record
     */
    public static function logRecord(CustomerEnquiry $customerEnquiry, CustomerEnquiryStatusType $type, User|int $causer): CustomerEnquiryStatusRecord
    {
        return CustomerEnquiryStatusRecord::create([
            'customer_enquiry_id' => $customerEnquiry->id,
            'status_id' => $customerEnquiry->{"{$type->value}_status_id"},
            'status_type' => $type,
            'remark' => $customerEnquiry->remark,
            'causer_id' => is_int($causer) ? $causer : $causer->id,
        ]);
    }

    /**
     * Get the related enquiry of the record
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(CustomerEnquiry::class);
    }

    /**
     * Get the status of the file
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CustomerEnquiryStatus::class, 'status_id');
    }

    /**
     * Get the causer of the record
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
