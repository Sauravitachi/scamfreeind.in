<?php

namespace App\Http\Requests\Admin;

use App\Enums\EscalationType;
use Illuminate\Validation\Rule;

class EscalationRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scam_id' => ['required', 'integer', 'gt:0', Rule::exists('scams', 'id')],
            'type' => ['required', 'string', Rule::in(EscalationType::array())],
            'message' => ['required', 'string', 'max:1000'],
            'file' => ['nullable', 'file', 'mimes:png,jpg,pdf,csv,xlsx,docx', 'max:10240'],
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
            'scam_id' => 'scam',
        ];
    }
}
