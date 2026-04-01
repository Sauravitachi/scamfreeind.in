<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'guard_name', 'user_creatable_roles', 'is_admin'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_creatable_roles' => 'array',
        'is_admin' => 'boolean',
    ];
}
