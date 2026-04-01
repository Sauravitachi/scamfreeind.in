<?php

namespace App\Http\Requests\Admin;

use App\Models\ScamLead;
use Closure;
use Illuminate\Validation\Validator;

class BulkDeleteScamLeadRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'gt:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after($this->validateScamLeadIds());
    }

    /**
     * Validate all provided IDs exist in the ScamLead table.
     */
    protected function validateScamLeadIds(): Closure
    {
        return function (Validator $validator) {
            $ids = $this->input('ids', []);

            // Fetch count of valid IDs in one query
            $existingCount = ScamLead::whereIn('id', $ids)->count();

            if ($existingCount !== count($ids)) {
                $validator->errors()->add('ids', 'Some provided ScamLead IDs are invalid.');
            }
        };
    }
}
