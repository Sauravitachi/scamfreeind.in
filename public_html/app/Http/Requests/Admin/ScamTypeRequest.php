<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScamTypeRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $scamTypeId = $this->route('scam_type');

        return [
            'slug' => [
                'required',
                'alpha_dash',
                'max:250',
                Rule::unique('scam_types', 'slug')->ignore($scamTypeId),
            ],
            'title' => [
                'required',
                'string',
                'max:250',
                Rule::unique('scam_types', 'title')->ignore($scamTypeId),
            ],
            'is_default' => ['nullable'],
        ];
    }
}
