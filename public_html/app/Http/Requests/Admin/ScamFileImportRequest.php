<?php

namespace App\Http\Requests\Admin;

class ScamFileImportRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1'],
            'data.*.name' => ['nullable', 'string', 'max:250'],
            'data.*.phone_number' => ['required', 'numeric', 'digits_between:9,14'],
            'data.*.email' => ['nullable', 'string', 'email', 'max:250'],
            'data.*.country_code' => ['nullable', 'string', $this->countryValidationRules()],
            'data.*.scam_type' => ['required', 'numeric', 'gt:0'],
            'data.*.scam_amount' => ['nullable', 'numeric', 'min:1', 'max:999999999999'],
            'data.*.sales_assignee' => ['nullable', 'numeric', 'gt:0'],
            'data.*.drafting_assignee' => ['nullable', 'numeric', 'gt:0'],
            'data.*.service_assignee' => ['nullable', 'numeric', 'gt:0'],
            'data.*.sales_status' => ['nullable', 'numeric', 'gt:0'],
            'data.*.drafting_status' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
