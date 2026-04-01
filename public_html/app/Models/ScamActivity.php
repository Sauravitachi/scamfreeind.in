<?php

namespace App\Models;

use App\Enums\ScamActivityEvent;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scam_id',
        'description',
        'user_id',
        'event',
        'notify_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'event' => ScamActivityEvent::class,
        'notify_at' => 'datetime',
    ];

    /**
     * Get the scam of the activity
     */
    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }

    /**
     * Get the user of the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
