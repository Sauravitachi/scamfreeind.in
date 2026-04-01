<?php

namespace App\Http\Requests\Admin;

class BusinessSettingsRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'office_start_time' => ['required', 'date_format:H:i'],
            'office_end_time' => ['required', 'date_format:H:i', 'after:office_start_time'],

            // Enquiry Freeze
            'hours_to_freeze_enquiries' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'starting_enquiries_relaxation_hours' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'freeze_enquiry_threshold' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'freeze_enquiry_release_threshold' => ['nullable', 'integer', 'min:0', 'max:99999'],

            // sales null status
            'hours_to_freeze_sales_null' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'freeze_sales_null_threshold' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'freeze_sales_null_release_threshold' => ['nullable', 'integer', 'min:0', 'max:99999'],

            // drafting null status
            'hours_to_freeze_drafting_null' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'freeze_drafting_null_threshold' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'freeze_drafting_null_release_threshold' => ['nullable', 'integer', 'min:0', 'max:99999'],

            // sales auto case assign
            'sales_auto_case_assign:threshold_case_count' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'sales_auto_case_assign:achieve_in_hours' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'sales_auto_case_assign:new_cases_count' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'sales_auto_case_assign:fresh_cases_date_range' => ['nullable', 'string'],
            'sales_auto_case_assign:lb_scam_amount' => ['nullable', 'integer', 'gte:0', 'max:9999999999'],
            'sales_auto_case_assign:allow_null_amount' => ['nullable'],
            'sales_auto_case_assign:missed_assign_notify_to_roles' => ['nullable', 'array'],

        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'office_start_time.required' => 'The office start time is required.',
            'office_start_time.date_format' => 'The office start time must be in the format HH:MM.',

            'office_end_time.required' => 'The office end time is required.',
            'office_end_time.date_format' => 'The office end time must be in the format HH:MM.',
            'office_end_time.after' => 'The office end time must be a time after the office start time.',
        ];
    }
}
