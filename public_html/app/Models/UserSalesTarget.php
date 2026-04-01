<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSalesTarget extends Model
{
    protected $fillable = [
        'user_id',
        'period_type',
        'starts_at',
        'ends_at',
        'target_amount',
        'target_points',
        'target_case_count',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
