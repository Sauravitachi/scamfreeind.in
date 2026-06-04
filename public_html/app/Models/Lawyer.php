<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lawyer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lawyers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The specializations (problem types) that this lawyer handles.
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(ProblemType::class, 'lawyer_specializations', 'lawyer_id', 'problem_type_id')
            ->withTimestamps();
    }

    /**
     * Get the lawyer leads assigned to this lawyer.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(LawyerLead::class, 'lawyer_id');
    }
}
