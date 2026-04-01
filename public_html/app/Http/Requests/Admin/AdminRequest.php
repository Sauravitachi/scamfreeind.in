<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;

class AdminRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $except = $request->admin ? ", {$request->admin->id}" : '';

        $rules = [
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'alpha_num', 'max:40', 'unique:admins,username'.$except],
            'email' => ['required', 'string', 'max:200', 'email'],
            'phone_number' => ['required', 'numeric', 'max_digits:14'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'exists:roles,name'],
        ];

        if (! $request->admin) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'roles.*.required' => 'At least 1 role must be selected.',
            'roles.*.exists' => 'The selected role is invalid.',
        ];
    }
}
