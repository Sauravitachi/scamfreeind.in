<?php

namespace App\Http\Requests\Admin;

class BulkRecycleScamRequest extends \App\Foundation\FormRequest
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
        ];
    }
}
