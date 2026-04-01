<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScamAssigneeType;
use App\Services\ScamService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignUserToScamRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request, ScamService $scamService): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ScamAssigneeType::array())],
            'assignee_id' => ['nullable', 'integer', 'gt:0', $this->validateAssigneeRule($this->input('type'))],
            'enquiry_id' => ['nullable', 'integer', 'gt:0', 'exists:customer_enquiries,id'],
        ];
    }

    /**
     * Custom validation rule for assignee validation.
     */
    protected function validateAssigneeRule(string $assigneeType): \Closure
    {
        $scamService = ScamService::getInstance();

        return function ($attribute, $value, $fail) use ($scamService, $assigneeType) {
            if (! $scamService->validateAssigneeId($value, $assigneeType)) {
                $fail("The selected {$attribute} is not a valid {$assigneeType} assignee.");
            }
        };
    }
}
