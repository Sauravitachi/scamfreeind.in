<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The name of the authentication guard being used.
     *
     * @var string
     */
    protected $guard_name = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_code',
        'phone_number',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        static::saving(function (Admin $admin) {
            // Check if the password is being changed
            if ($admin->isDirty('password') && Hash::needsRehash($admin->password)) {
                // Hash the password before saving/updating
                $admin->password = Hash::make($admin->password);
            }
        });
    }

    public static function getByUsername(string $username, array|string $columns = ['*']): ?Admin
    {
        return Admin::where('username', $username)->first($columns);
    }

    /**
     * Get the role with  high priority
     */
    public function getPrimaryRoleAttribute(): ?SpatieRole
    {
        return $this->roles->sortBy('priority')->first() ?? null;
    }
}
