<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecializationRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $problemTypeId = $this->route('specialization')?->id ?? $this->route('specialization');

        return [
            'slug' => [
                'required',
                'alpha_dash',
                'max:250',
                Rule::unique('problem_types', 'slug')->ignore($problemTypeId),
            ],
            'title' => [
                'required',
                'string',
                'max:250',
                Rule::unique('problem_types', 'title')->ignore($problemTypeId),
            ],
            'is_default' => ['nullable'],
        ];
    }
}
