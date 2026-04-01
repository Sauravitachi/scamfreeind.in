<?php

namespace App\Models;

use App\Foundation\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['tag', 'key', 'value'];
}
