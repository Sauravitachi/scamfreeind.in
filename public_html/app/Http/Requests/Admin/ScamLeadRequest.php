<?php

namespace App\Http\Requests\Admin;

use App\Foundation\FormRequest;
use Illuminate\Validation\Rule;

class ScamLeadRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone_number' => $this->input('phone_number') ?? $this->input('phone'),
            'customer_description' => $this->input('customer_description') ?? $this->input('description'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:250'],
            'email' => ['nullable', 'string', 'email', 'max:250'],
            'country_code' => ['nullable', 'string', $this->countryValidationRules()],
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
            'scam_source_id' => ['nullable', 'integer', Rule::exists('scam_sources', 'id')],
            'scam_type_id' => ['nullable', 'integer', Rule::exists('scam_types', 'id')],
            'scam_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'customer_description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'customer_description' => 'description',
            'scam_source_id' => 'scam source',
            'scam_type_id' => 'scam type',
        ];
    }
}
