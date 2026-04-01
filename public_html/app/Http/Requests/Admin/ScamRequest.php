<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ScamRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $scamId = $this->route('scam');

        $customerIdRules = $scamId ? [
            'prohibited',
        ] : [
            'required',
            'integer',
            Rule::exists('customers', 'id'),
        ];

        return [
            'customer_id' => $customerIdRules,
            'scam_type_id' => ['nullable', 'integer', Rule::exists('scam_types', 'id')],
            'scam_amount' => ['nullable', 'numeric', 'max:999999999999'],
            'scam_source_id' => ['nullable', 'integer', Rule::exists('scam_sources', 'id')],
            'customer_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customer_id' => 'customer',
            'scam_type_id' => 'scam type',
        ];
    }
}
