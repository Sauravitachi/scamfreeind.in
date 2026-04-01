<?php

namespace App\Http\Requests\Admin;

use App\Constants\Permission;
use App\Services\ScamService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkAssignUserToScamRequest extends \App\Foundation\FormRequest
{
    public function validateStatusId(string $type, int $statusId, Permission $permission): bool
    {
        $status = DB::table('scam_statuses')
            ->where('is_file_required', 0)
            ->where('id', $statusId)->where('type', $type)->first();

        return $status->is_lock ? request()->user()->can($permission->value) : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        return [
            'scams' => ['required', 'array', 'min:1', 'max:1000'],
            'scams.*' => ['integer', 'gte:0'],
            'customer_enquiries' => ['nullable', 'array', 'min:1', 'max:1000'],
            'customer_enquiries.*' => ['integer', 'gte:0'],
            'sales_assignee_id' => ['nullable', 'integer', 'gte:0', $this->validateAssigneeRule('sales')],
            'drafting_assignee_id' => ['nullable', 'integer', 'gte:0', $this->validateAssigneeRule('drafting')],
            'service_assignee_id' => ['nullable', 'integer', 'gte:0', $this->validateAssigneeRule('service')],
            'sub_admin_id' => ['nullable', 'integer', 'gte:0', $this->validateAssigneeRule('sub_admin')],
            'sales_status_id' => ['nullable', 'integer', 'gte:0', $this->validateStatusRule('sales')],
            'drafting_status_id' => ['nullable', 'integer', 'gte:0', $this->validateStatusRule('drafting')],
            'source_id' => ['nullable', 'integer', 'gte:0', 'exists:scam_sources,id'],
        ];
    }

    /**
     * Custom validation rule for assignee validation.
     */
    protected function validateAssigneeRule(string $assigneeType): Closure
    {
        $scamService = ScamService::getInstance();

        return function ($attribute, $value, $fail) use ($scamService, $assigneeType) {
            if ($value == 0) {
                return;
            } // pass
            if (! $scamService->validateAssigneeId($value, $assigneeType)) {
                $fail("The selected {$attribute} is not a valid {$assigneeType} assignee.");
            }
        };
    }

    /**
     * Custom validation rule for status validation.
     */
    protected function validateStatusRule(string $statusType): Closure
    {
        return function ($attribute, $value, $fail) use ($statusType) {
            if ($value == 0) {
                return; // pass
            }
            $exists = DB::table('scam_statuses')
                ->where('is_file_required', 0)
                ->where('id', $value)->where('type', $statusType)->exists();
            if (! $exists) {
                $fail("The selected {$attribute} is not a valid {$statusType} status.");
            }
        };
    }
}
