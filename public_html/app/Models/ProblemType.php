<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProblemType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'problem_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['slug', 'title', 'is_default'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::saving(function (ProblemType $problemType) {
            if ($problemType->isDirty('is_default')) {
                $status = (bool) $problemType->is_default;
                if ($status) {
                    ProblemType::where('is_default', 1)->update(['is_default' => 0]);
                }
                $problemType->is_default = $status;
            }
        });
    }

    /**
     * Get the default problem type.
     */
    public static function default(array|string $columns = ['*']): ?ProblemType
    {
        return ProblemType::where('is_default', 1)->first($columns);
    }

    /**
     * Get all lawyer leads associated with this problem type.
     */
    public function lawyerLeads(): HasMany
    {
        return $this->hasMany(LawyerLead::class, 'problem_type_id');
    }
}
