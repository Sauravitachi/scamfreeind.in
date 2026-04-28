<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class StateRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $stateId = $this->route('state');

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('states', 'name')->ignore($stateId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('states', 'code')->ignore($stateId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
        ]);
    }
}
