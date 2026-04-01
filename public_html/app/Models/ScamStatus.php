<?php

namespace App\Models;

use App\Enums\ScamStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScamStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'index',
        'slug',
        'title',
        'type',
        'notify_after_days',
        'customer_enquiry_notify_role_id',
        'cap_scams',
        'cap_last_days',
        'is_file_required',
        'is_data_update_required',
        'is_scam_type_update_required',
        'is_lock',
        'is_approval_required',
        'bypass_enquiry',
        'is_freezable',
        'unassign_scam',
        'hours_to_freeze',
        'freeze_scams_threshold',
        'freeze_release_scams_threshold',
        'unassign_scam_in_days',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => ScamStatusType::class,
        'is_file_required' => 'boolean',
        'is_data_update_required' => 'boolean',
        'is_scam_type_update_required' => 'boolean',
        'is_lock' => 'boolean',
        'is_approval_required' => 'boolean',
        'bypass_enquiry' => 'boolean',
        'is_freezable' => 'boolean',
        'unassign_scam' => 'boolean',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::saving(function (ScamStatus $scamStatus) {

            $fields = ['is_file_required', 'is_data_update_required', 'is_scam_type_update_required', 'is_lock', 'is_approval_required',
                'bypass_enquiry', 'is_freezable', 'unassign_scam'];

            foreach ($fields as $field) {
                if ($scamStatus->isDirty($field)) {
                    $scamStatus->$field = (bool) $scamStatus->$field;
                }
            }

        });

    }

    public static function scopeWhereTypeSales(Builder $query): void
    {
        $query->where('type', ScamStatusType::SALES);
    }

    public static function scopeWhereTypeDrafting(Builder $query): void
    {
        $query->where('type', ScamStatusType::DRAFTING);
    }

    /**
     * Get the statuses that can come *after* this status.
     */
    public function nextStatuses(): BelongsToMany
    {
        return $this->belongsToMany(
            related: ScamStatus::class,
            table: 'status_transitions',
            foreignPivotKey: 'current_status_id',
            relatedPivotKey: 'next_status_id'
        );
    }

    /**
     * Get the statuses that can come *before* this status.
     */
    public function previousStatuses(): BelongsToMany
    {
        return $this->belongsToMany(
            related: ScamStatus::class,
            table: 'status_transitions',
            foreignPivotKey: 'next_status_id',
            relatedPivotKey: 'current_status_id'
        );
    }

    /**
     * Get the update fields for this status update
     */
    public function statusUpdateFields(): HasMany
    {
        return $this->hasMany(ScamStatusUpdateField::class, 'scam_status_id');
    }

     public function isRegistered(): bool
    {
        return $this->slug === 'registered';
    }

    public function isNotInterested(): bool
    {
        return $this->slug === 'not_interested';
    }

    public static function registeredId(): ?int
    {
        return static::where('slug', 'registered')->value('id');
    }
}
