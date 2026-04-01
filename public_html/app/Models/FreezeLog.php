<?php

namespace App\Models;

use App\Enums\FreezeType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreezeLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'freeze',
        'user_id',
        'status_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => FreezeType::class,
        'freeze' => 'boolean',
    ];

    /**
     * Get the user of the freeze log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status of the freeze log
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class);
    }
}
