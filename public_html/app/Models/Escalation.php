<?php

namespace App\Models;

use App\Enums\EscalationStatus;
use App\Enums\EscalationType;
use App\Foundation\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escalation extends Model
{
    public const BASE_TRACK_NUMBER = 100000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['scam_id', 'escalated_by_user_id', 'type', 'status', 'is_rejected'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => EscalationStatus::class,
        'type' => EscalationType::class,
        'closed_at' => 'datetime',
        'is_rejected' => 'boolean',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::created(function (Escalation $escalation): void {
            // After the record has been created, set the track_id
            $escalation->track_id = Escalation::BASE_TRACK_NUMBER + $escalation->id;
            $escalation->saveQuietly(); // Avoid triggering events again
        });

        static::saving(function (Escalation $e) {

            if ($e->isDirty('status') && $e->status === EscalationStatus::CLOSED) {
                $e->closed_at = now();
            }

        });
    }

    public static function scopeWhereUserAssociated(Builder $query, AuthUser|User $user): void
    {
        $query->where('escalated_by_user_id', $user->id)
            ->orWhereHas('scam', function ($query) use ($user) {
                $query->where('sales_assignee_id', $user->id)->orWhere('drafting_assignee_id', $user->id)->orWhere('service_assignee_id', $user->id);
            });
    }

    public function isUserAssociated(AuthUser|User $user): bool
    {
        $c1 = $this->escalated_by_user_id === $user->id;

        $c2 = $this->scam()->where(function (Builder $q) use ($user) {
            $q->where('sales_assignee_id', $user->id)
                ->orWhere('drafting_assignee_id', $user->id)
                ->orWhere('service_assignee_id', $user->id);
        })->exists();

        return $c1 or $c2;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === EscalationStatus::CLOSED;
    }

    /**
     * Get the scam of the escalation
     */
    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }

    /**
     * Get the escalation chats of the escalation
     */
    public function chats(): HasMany
    {
        return $this->hasMany(EscalationChat::class);
    }

    /**
     * Get the User by which escalation has been created
     */
    public function escalatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by_user_id');
    }
}
