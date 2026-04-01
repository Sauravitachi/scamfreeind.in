<?php

namespace App\Models;

use App\Foundation\Model;

class StatusTransition extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'current_status_id',
        'next_status_id',
    ];
}
