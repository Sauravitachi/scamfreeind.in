<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LawyerRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $lawyerId = $this->route('lawyer')?->id ?? $this->route('lawyer');

        $rules = [
            'name' => ['required', 'string', 'max:250'],
            'email' => [
                'nullable',
                'email',
                'max:250',
                Rule::unique('lawyers', 'email')->ignore($lawyerId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpeg,png,jpg,gif,webp'],
            'address' => ['nullable', 'string', 'max:250'],
            'is_active' => ['nullable'],
            'specializations' => ['nullable', 'array'],
        ];

        // Omit wildcard rules for GET requests to prevent Proengsoft JsValidation from failing on multi-select array inputs on client-side
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $rules['specializations.*'] = ['integer', Rule::exists('problem_types', 'id')];
        }

        return $rules;
    }
}
