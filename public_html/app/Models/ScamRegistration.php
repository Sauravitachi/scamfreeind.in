<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamRegistration extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scam_id',
        'scam_assigned_id',
        'scam_registration_amount_id',
        'causer_id',
        'caused_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'caused_at' => 'datetime',
    ];

    /**
     * Get the causer of the registration
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scamRegistrationAmount(): BelongsTo
    {
        return $this->belongsTo(ScamRegistrationAmount::class, 'scam_registration_amount_id');
    }

    public function scam(): BelongsTo
    {
        return $this->belongsTo(Scam::class);
    }
}
