<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScamType extends Model
{
    use HasFactory;

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

        static::saving(function (ScamType $scamType) {
            if ($scamType->isDirty('is_default')) {
                $status = (bool) $scamType->is_default;
                if ($status) {
                    ScamType::where('is_default', 1)->update(['is_default' => 0]);
                }
                $scamType->is_default = $status;
            }

        });
    }

    public static function default(array|string $columns = ['*']): ScamType
    {
        return ScamType::where('is_default', 1)->first($columns);
    }

    /**
     * Get all scams of this type.
     */
    public function scams()
    {
        return $this->hasMany(Scam::class);
    }
}
