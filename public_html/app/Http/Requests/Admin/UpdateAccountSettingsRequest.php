<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateAccountSettingsRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $userId = $request->user()?->id;

        return [
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:40', Rule::unique('users')->ignore($userId)],
            'email' => ['required', 'string', 'max:200', 'email', Rule::unique('users')->ignore($userId)],
            'country_code' => ['nullable', 'string', $this->countryValidationRules()],
            'phone_number' => ['required', 'numeric', 'digits_between:10,14'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5048'],
            'delete_avatar' => ['nullable', 'boolean'],
        ];
    }
}
