<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class CustomerRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:250', 'email', Rule::unique('customers', 'email')->ignore($customerId)],
            'country_code' => ['nullable', 'string', $this->countryValidationRules()],
            'phone_number' => ['required', 'numeric', Rule::unique('customers', 'phone_number')->ignore($customerId)],
        ];
    }
}
