<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'dob',
        'pob',
        'tob',
        'acharya_name',
        'message',
    ];
}
