<?php

namespace App\Models;

use App\Enums\ScamStatusFieldType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamStatusUpdateField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'scam_status_id',
        'status_field_type',
        'is_required',
        'prefill_previous_value',
        'order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status_field_type' => ScamStatusFieldType::class,
        'is_required' => 'boolean',
        'prefill_previous_value' => 'boolean',
    ];

    public function scamStatus(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class);
    }
}
