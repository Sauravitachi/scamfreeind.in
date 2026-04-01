<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class BulkUpdateScamRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scams' => ['required', 'array', 'min:1', 'max:1000'],
            'scams.*' => ['integer', 'gte:0'],
            'scam_amount' => ['nullable', 'numeric', 'min:1', 'max:999999999999'],
            'scam_type_id' => ['nullable', 'integer', Rule::exists('scam_types', 'id')],
            'scam_source_id' => ['nullable', 'integer', Rule::exists('scam_sources', 'id')],
        ];
    }
}
