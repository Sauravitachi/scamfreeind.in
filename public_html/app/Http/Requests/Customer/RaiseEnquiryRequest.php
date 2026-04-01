<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;

class RaiseEnquiryRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scam_id' => ['required', 'integer', Rule::exists('scams', 'id')],
            'query' => ['required', 'string', 'max:1000'],
        ];
    }
}
