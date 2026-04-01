<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Validation\Rule;

class LoginRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'identifier' => ['required', 'string'],
            'password' => ['required'],
        ];

        // Conditionally add turnstile rule if the key is present
        if (config('services.turnstile.key')) {
            $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'identifier' => 'Email/Username',
            'cf-turnstile-response' => 'Captcha',
        ];
    }
}
