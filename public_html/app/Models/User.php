<?php

namespace App\Models;

use App\Constants\Permission as PermissionConstant;
use App\Enums\PreferenceKey;
use App\Contracts\UserDetailTextContract;
use App\Contracts\WhatsappProfileContract;
use App\Services\HelperService;
use App\Traits\Models\HasPhoneNumber;
use App\Traits\ModelSupport;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Rinvex\Country\CountryLoader;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;
use App\Models\ScamRegistration;

class User extends Authenticatable implements UserDetailTextContract
{
    use HasFactory, Notifiable;
    use HasPhoneNumber;
    use HasRoles;
    use ModelSupport;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'country_code',
        'phone_number',
        'password',
        'status',
        'last_pinged_at',
        'freeze_disabled_until',
        'login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const USER_LOGIN_THRESHOLD = 30; // in seconds

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_pinged_at' => 'datetime',
            'freeze_disabled_until' => 'datetime',
            'login_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::saving(function (User $user) {

            if ($user->isDirty('password') && Hash::needsRehash($user->password)) {
                $user->password = Hash::make($user->password);
            }

            // if ($user->isDirty('country_code') && $user->country_code) {
            //     $user->dial_code = CountryLoader::country($user->country_code)?->getCallingCode();
            // }

        });
    }

    public static function scopeWhereIdOrUsername(Builder $query, string $idOrUsername): void
    {
        $query->where('id', $idOrUsername)->orwhere('username', $idOrUsername);
    }

    public static function scopeWhereLoggedIn(Builder $query): void
    {
        $time = now()->subSeconds(User::USER_LOGIN_THRESHOLD);

        $query->whereNotNull('login_at')
            ->where('last_pinged_at', '>', $time);
    }

    public static function scopeWhereNotLoggedIn(Builder $query): void
    {
        $time = now()->subSeconds(User::USER_LOGIN_THRESHOLD);

        $query->whereNull('login_at')->orWhereNull('last_pinged_at')->orWhere('last_pinged_at', '<', $time);
    }

    public static function scopeWhereSales(Builder $query): void
    {
        $query->permission([PermissionConstant::SALES_MANAGEMENT, PermissionConstant::SALES_MANAGEMENT_SELF]);
    }

    public static function scopeWhereDrafting(Builder $query): void
    {
        $query->permission([PermissionConstant::DRAFTING_MANAGEMENT, PermissionConstant::DRAFTING_MANAGEMENT_SELF]);
    }

    public static function scopeWhereService(Builder $query): void
    {
        $query->permission([PermissionConstant::SERVICE_MANAGEMENT, PermissionConstant::SERVICE_MANAGEMENT_SELF]);
    }

    public static function scopeWhereSubAdmin(Builder $query): void
    {
        $query->permission([PermissionConstant::SUB_ADMIN_MANAGEMENT]);
    }

    public static function scopeUnderSubAdmin(Builder $query, int|User $subAdmin): void
    {
        $id = $subAdmin instanceof User ? $subAdmin->id : $subAdmin;
        $query->whereHas('preferences', function (Builder $q) use ($id) {
            $q->where('key', \App\Enums\PreferenceKey::SUB_ADMIN_ID)->where('value', $id);
        });
    }

    public function getRoleString(): ?string
    {
        $type = $this->userType();

        return in_array($type, ['sales', 'drafting']) ? $type : null;
    }

    public static function getByUsername(string $username, array|string $columns = ['*']): ?User
    {
        return User::where('username', $username)->first($columns);
    }


    public function getNameWithUsernameAttribute(): string
    {
        if (! $this->hasAllAttributes('name', 'username')) {
            $this->refresh();
        }
        return "$this->name ($this->username)";
    }

    public function getUserDetailText(): ?string
    {
        return $this->getNameWithUsernameAttribute();
    }

    public function getFullNameAttribute(): ?string
    {
        return $this->name;
    }

    public function getUsernameWithRoleNameAttribute(): string
    {
        if (! $this->hasAllAttributes('username')) {
            $this->refresh();
        }
        if (! isset($this->role->name)) {
            $this->load('roles:id,name');
        }
        $role = $this->roles?->first()?->name ?? 'Member';
        return "$this->username ($role)";
    }

    /**
     * Check if all given attributes are set and not null on the model.
     */
    public function hasAllAttributes(string ...$attributes): bool
    {
        foreach ($attributes as $attribute) {
            if (!array_key_exists($attribute, $this->attributes) || is_null($this->attributes[$attribute])) {
                return false;
            }
        }
        return true;
    }

    public function getWhatsappNameAttribute(): ?string
    {
        $userName = trim($this->name ?? '');
        if (empty($userName)) {
            $userName = 'User';
        }

        return $userName;
    }

    public function getFullPhoneNumberAttribute(): string
    {
        $dialCode = $this->dial_code ? '+'.ltrim($this->dial_code, '+') : '';

        return trim("{$dialCode} {$this->phone_number}");
    }

    // public function getCountryWithEmojiAttribute(): ?string
    // {
    //     if ($this->country_code) {
    //         try {
    //             $country = country($this->country_code);

    //             return $country->getEmoji().' '.$country->getName();
    //         } catch (Exception $e) {
    //             return null;
    //         }
    //     }

    //     return null;
    // }

    public function userType(): string
    {
        return userType($this->roles->first());
    }

    public function isFreezeForceReleased(): bool
    {
        return $this->freeze_disabled_until && ($this->freeze_disabled_until > now());
    }

    public function isLoggedIn(): bool
    {
        return $this->login_at && ($this->last_pinged_at > now()->subSeconds(User::USER_LOGIN_THRESHOLD));
    }

    public function hasTodayActivity(): bool
    {
        if (! $this->lastActivity?->created_at) {
            return false;
        }

        $activityTime = $this->lastActivity->created_at;
        $officeTimings = once(fn () => HelperService::getInstance()->getOfficeTiming());

        if ($officeTimings === null) {
            return false;
        }

        return $activityTime->between($officeTimings[0], $officeTimings[1]);
    }

    public function getHasTodayActivityAttribute(): bool
    {
        return $this->hasTodayActivity();
    }

    /**
     * Get the role with  high priority
     */
    public function getPrimaryRoleAttribute(): ?SpatieRole
    {
        return $this->roles->sortBy('id')->first() ?? null;
    }

    public function getProfileAvatarAttribute(): string
    {
        return Storage::url($this->avatar);
    }

    public function getPreferencesMapAttribute(): Collection
    {
        return $this->preferences->mapWithKeys(fn (UserPreference $item): array => [$item->key->value => $item->value]);
    }

    public function getSubAdminIdAttribute(): ?int
    {
        return (int) ($this->preferencesMap->get(PreferenceKey::SUB_ADMIN_ID->value) ?? 0) ?: null;
    }

    /**
     * Get all scams where the user is the sales assignee.
     */
    public function salesAssignedScams(): HasMany
    {
        return $this->hasMany(Scam::class, 'sales_assignee_id');
    }

    /**
     * Get all scams where the user is the drafting assignee.
     */
    public function draftingAssignedScams(): HasMany
    {
        return $this->hasMany(Scam::class, 'drafting_assignee_id');
    }

    /**
     * Get all scams where the user is the service assignee.
     */
    public function serviceAssignedScams(): HasMany
    {
        return $this->hasMany(Scam::class, 'service_assignee_id');
    }

    /**
     * Get all the activities of the user
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'causer');
    }

    /**
     * Get the latest activity of the user
     */
    public function lastActivity(): MorphOne
    {
        return $this->morphOne(Activity::class, 'causer')->latestOfMany();
    }

    /**
     * Get the profile picture model instance
     */
    public function profilePicture(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'profile_picture_id');
    }

    /**
     * Get all preferences of the user
     */
    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Get scam status freezes of the user
     */
    public function scamStatusFreezes(): HasMany
    {
        return $this->hasMany(UserScamStatusFreeze::class);
    }

    /**
     * Get customer enquiry freeze of the user
     */
    public function customerEnquiryFreezes(): HasOne
    {
        return $this->hasOne(CustomerEnquiryFreeze::class);
    }

    /**
     * Get sales targets for the user
     */
    public function salesTargets(): HasMany
    {
        return $this->hasMany(UserSalesTarget::class);
    }

    /**
     * Get the current active sales target
     */
    public function getCurrentTargetAttribute(): ?UserSalesTarget
    {
        return $this->salesTargets()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    /**
     * Get actual sales amount for a given period
     */
    public function getActualSalesAmount($startsAt, $endsAt): float
    {
        $role = $this->getRoleString();
        $column = "{$role}_assignee_id";

        if (!in_array($role, ['sales', 'drafting'])) {
            return 0;
        }

        return (float) ScamRegistration::whereHas('scam', function ($query) use ($column) {
            $query->where($column, $this->id);
        })
        ->whereBetween('caused_at', [$startsAt, $endsAt])
        ->with('scamRegistrationAmount')
        ->get()
        ->sum(function ($registration) {
            return $registration->scamRegistrationAmount?->amount ?? 0;
        });
    }
}
