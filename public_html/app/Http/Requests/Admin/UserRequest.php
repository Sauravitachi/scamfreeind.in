<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request, UserService $service): array
    {

        $userId = $this->route('user');

        $currentRole = $request->user()?->roles()->first();
        $allowedRoleIds = json_decode($currentRole?->user_creatable_roles ?? '[]');
        $allowedRoleNames = Role::whereIn('id', $allowedRoleIds)->pluck('name')->toArray();

        $rules = [
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:40', Rule::unique('users')->ignore($userId)],
            'email' => ['required', 'string', 'max:200', 'email', Rule::unique('users')->ignore($userId)],
            'country_code' => ['nullable', 'string', $this->countryValidationRules()],
            'phone_number' => ['required', 'numeric', 'digits_between:10,14'],
            'role' => [
                'required',
                'string',
                Rule::in($allowedRoleNames),
            ],

        ];

        if (! $userId) {
            $rules['password'] = ['required', 'string', 'min:8'];
            $rules['confirm_password'] = ['required', 'string', 'same:password'];
        }

        return $rules;
    }
}
