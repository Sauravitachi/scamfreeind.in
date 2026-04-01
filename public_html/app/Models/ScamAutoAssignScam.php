<?php

namespace App\Models;

use App\Foundation\Model;

class ScamAutoAssignScam extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'target_scam_id',
        'assigned_scams_batch_id',
    ];
}
