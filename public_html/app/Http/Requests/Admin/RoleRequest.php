<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof \App\Models\Role ? $role->id : $role;
        return [
            'name' => ['required', 'string', 'max:250', "unique:roles,name,{$roleId}"],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
            'user_creatable_roles' => ['nullable', 'array'],
            'is_admin' => ['nullable', 'boolean'],
        ];
    }
}
