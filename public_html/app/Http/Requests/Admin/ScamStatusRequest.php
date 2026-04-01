<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScamStatusFieldType;
use App\Enums\ScamStatusType;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScamStatusRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $scamStatus = $this->route('scam_status');

        $totalScamUpdateFieldTypes = ScamStatusFieldType::count();

        $rules = [
            'index' => [
                'required',
                'integer',
                'gte:1',
                'max:99999',
                Rule::unique('scam_statuses', 'index')->where(function (Builder $query) use ($request, $scamStatus) {
                    $type = $scamStatus ? $scamStatus->type : $request->input('type');
                    $query->where('type', $type);
                })->ignore($scamStatus),
            ],
            'slug' => [
                'required',
                'alpha_dash',
                'max:250',
                Rule::unique('scam_statuses', 'slug')->ignore($scamStatus),
            ],
            'title' => ['required', 'string', 'max:250'],
            'notify_after_days' => ['nullable', 'integer', 'min:1', 'max:9999999'],
            'customer_enquiry_notify_role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')],
            'cap_scams' => ['nullable', 'integer', 'gt:0', 'max:99999'],
            'cap_last_days' => ['nullable', 'integer', 'gt:0', 'max:99999'],
            'is_file_required' => ['nullable'],
            'is_data_update_required' => ['nullable'],
            'is_scam_type_update_required' => ['nullable'],
            'is_lock' => ['nullable'],
            'is_approval_required' => ['nullable'],
            'bypass_enquiry' => ['nullable'],
            'is_freezable' => ['nullable'],
            'unassign_scam' => ['nullable'],
            'unassign_scam_in_days' => ['nullable', 'integer', 'gte:0', 'max:999'],
            'updatable_fields' => ['nullable', 'array', "max:$totalScamUpdateFieldTypes"],
            'updatable_fields.*.status_field_type' => ['required', 'string', Rule::in(ScamStatusFieldType::array())],
            'updatable_fields.*.is_required' => ['nullable', 'boolean'],
            'updatable_fields.*.prefill_previous_value' => ['nullable', 'boolean'],
        ];

        if (! $scamStatus) {
            $rules['type'] = ['required', 'string', Rule::in(ScamStatusType::array())];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'customer_enquiry_notify_role_id' => 'customer enquiry notify role',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'updatable_fields.*.status_field_type.required' => '',
            'updatable_fields.*.status_field_type.string' => '',
            'updatable_fields.*.status_field_type.in' => '',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // hours_to_freeze validation
        $validator->sometimes(
            attribute: ['hours_to_freeze'],
            rules: ['required', 'integer', 'min:1', 'max:4380'],
            callback: fn () => $this->boolean('is_freezable')
        );

        // freeze_scams_threshold validation
        $validator->sometimes(
            attribute: ['freeze_scams_threshold', 'freeze_release_scams_threshold'],
            rules: ['required', 'integer', 'min:0', 'max:99999'], // adjust if needed
            callback: fn () => $this->boolean('is_freezable')
        );

        // Enforce uniqueness for status_field_type inside updatable_fields
        $validator->after(function ($validator) {
            $fields = $this->input('updatable_fields', []);

            if (! is_array($fields)) {
                return;
            }

            $typeIndexes = [];

            foreach ($fields as $index => $field) {
                $type = $field['status_field_type'] ?? null;

                if (! $type) {
                    continue;
                }

                // Group all indexes that share the same status_field_type
                $typeIndexes[$type][] = $index;
            }

            foreach ($typeIndexes as $type => $indexes) {
                if (count($indexes) > 1) {
                    foreach ($indexes as $i) {
                        $validator->errors()->add(
                            "updatable_fields[$i][status_field_type]",
                            'No more than 1 fields can have same field type'
                        );
                    }
                }
            }
        });

    }
}
