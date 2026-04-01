<?php

namespace App\Models;

use App\Enums\MissedAutoScamAssignResolutionStatus;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissedAutoScamAssignRecord extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'resolution_status' => MissedAutoScamAssignResolutionStatus::PENDING,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'achieve_in_hours',
        'threshold_case_count',
        'new_cases_count',
        'resolution_status',
        'resolved_by',
        'resolved_at',
        'resolution_remark',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'resolution_status' => MissedAutoScamAssignResolutionStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
