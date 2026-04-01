<?php

namespace App\Models;

use App\Enums\ScamAssigneeType;
use App\Foundation\Model;

class AutoScamAssignRecord extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'scam_id',
        'assignee_type',
        'batch_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'assignee_type' => ScamAssigneeType::class,
    ];
}
