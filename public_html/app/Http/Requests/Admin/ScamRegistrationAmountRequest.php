<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ScamRegistrationAmountRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $scamRegistrationAmount = $this->route('scam_registration_amount');

        return [
            'title' => ['required', 'string', 'max:200', Rule::unique('scam_registration_amounts')->ignore($scamRegistrationAmount)],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'points' => ['nullable', 'numeric', 'gt:0', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
