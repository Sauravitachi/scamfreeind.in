<?php

namespace App\Models;

use App\Contracts\UserDetailTextContract;
use App\Contracts\WhatsappProfileContract;
use App\Foundation\Model;
use App\Traits\Models\HasPhoneNumber;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rinvex\Country\CountryLoader;

class Customer extends Model implements UserDetailTextContract, WhatsappProfileContract
{
    use HasFactory, HasPhoneNumber;

    public const BASE_TRACK_NUMBER = 100000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'country_code',
        'phone_number',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted(): void
    {

        static::creating(function (Customer $customer): void {

            if ($customer->country_code === null || empty(trim($customer->country_code))) {
                $customer->country_code = 'in'; // default country code
            }

            $customer->dial_code = CountryLoader::country($customer->country_code)?->getCallingCode();

        });

        static::created(function (Customer $customer): void {
            // After the record has been created, set the track_id
            $customer->track_id = Customer::BASE_TRACK_NUMBER + $customer->id;
            $customer->saveQuietly(); // Avoid triggering events again

            ScamLead::wherePhoneDetails($customer->phone_number, $customer->country_code)->update(['existing_customer_id' => $customer->id]);
        });

        static::saving(function (Customer $customer): void {

            if ($customer->isDirty('country_code') && $customer->country_code) {
                $customer->dial_code = CountryLoader::country($customer->country_code)?->getCallingCode();
            }

        });
    }

    public static function scopeWhereSearchName(Builder $query, string $search): void
    {
        $query->whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ['%'.strtolower($search).'%']);
    }

    public static function scopeWhereSearchPhoneNumber(Builder $query, string $search): void
    {
        $query->whereRaw("CONCAT('+', dial_code, ' ', phone_number) LIKE ?", ["%{$search}%"]);
    }

    public static function scopeWhereSearch(Builder $query, string $search): void
    {
        $query->where(function ($query) use ($search) {
            $query->whereSearchName($search)
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere(function (Builder $q) use ($search) {
                    $q->whereSearchPhoneNumber($search);
                })
                ->orWhere('track_id', 'LIKE', "%{$search}%");
        });
    }

    public static function scopeWherePhoneDetails(Builder $query, string $phoneNumber, string $countryCode): void
    {
        $query->where('phone_number', $phoneNumber)
            ->where('country_code', $countryCode ?? 'in')
            ->latest();
    }

    public function getFullNameAttribute(): ?string
    {
        if (! $this->hasAllAttributes('first_name', 'last_name')) {
            $this->refresh();
        }
        $fullName = $this->first_name;
        if ($this->last_name) {
            $fullName .= " $this->last_name";
        }

        return $fullName;
    }

    public function getWhatsappNameAttribute(): ?string
    {
        $customerName = trim($this->getFullNameAttribute() ?? '');
        if (empty($customerName)) {
            $customerName = 'Customer';
        }

        return $customerName;
    }

    public function getFullNameWithFullPhoneNumberAttribute(): string
    {
        return $this->fullName ? "$this->fullName ($this->fullPhoneNumber)" : $this->fullPhoneNumber;
    }

    public function getUserDetailText(): ?string
    {
        return $this->getFullNameWithFullPhoneNumberAttribute();
    }

    public function getCountryWithEmojiAttribute(): ?string
    {
        if ($this->country_code) {
            try {
                $country = country($this->country_code);

                return $country->getEmoji().' '.$country->getName();
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get all scams associated with the customer.
     */
    public function scams(): HasMany
    {
        return $this->hasMany(Scam::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(CustomerEnquiry::class);
    }

    public function mainRegisteredScams(): HasMany
    {
        return $this->scams()->where('is_duplicate', false)->where('sales_status_id', 6)->oldest()->limit(1);
    }
}
