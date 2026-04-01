<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamLead extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'dial_code',
        'phone_number',
        'scam_amount',
        'scam_type_id',
        'customer_description',
        'source',
        'is_duplicate',
        'existing_customer_id',
        'scam_source_id',
        'count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'errors' => 'array',
        'is_duplicate' => 'boolean',
    ];

    public static function scopeWherePhoneDetails(Builder $query, string $phoneNumber, ?string $countryCode = 'in'): void
    {
        $query->where('phone_number', $phoneNumber)
            ->where('country_code', $countryCode ?? 'in')
            ->latest();
    }

    public function getFullPhoneNumberAttribute(): string
    {
        $phoneNumber = (string) $this->phone_number;
        $dialCode = (string) $this->dial_code;

        if ($dialCode !== '') {
            return "+$dialCode $phoneNumber";
        }

        return $phoneNumber;
    }

    public function getNameAttribute(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $invalidPlaceholders = ['{{name}}', '{{full_name}}'];
        
        return in_array(strtolower(trim($name)), $invalidPlaceholders) ? null : $name;
    }

    public function existingCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'existing_customer_id');
    }

    /**
     * Get the scam source of the scam
     */
    public function scamSource(): BelongsTo
    {
        return $this->belongsTo(ScamSource::class, 'scam_source_id');
    }

    public function scamType(): BelongsTo
    {
        return $this->belongsTo(ScamType::class, 'scam_type_id');
    }
}
