<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScamStatusType;
use App\Models\ScamStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ChangeScamStatusRequest extends \App\Foundation\FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $scam = $this->route('scam');
        $ignoreEmail = $scam && $scam->customer ? $scam->customer->email : null;

        return [
            'type' => ['required', 'string', Rule::in([ScamStatusType::SALES, ScamStatusType::DRAFTING])],
            'status_id' => ['nullable', 'integer', Rule::exists('scam_statuses', 'id')],
            'files' => ['nullable', 'array'],
            'files.*' => ['integer', 'gt:0'],

            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:250', 'email', Rule::unique('customers', 'email')->ignore($ignoreEmail, 'email')],
            'scam_amount' => ['nullable', 'numeric', 'min:1', 'max:999999999999'],
            'scam_type_id' => ['nullable', 'integer', Rule::exists('scam_types', 'id')],
            'scam_registration_amount_id' => ['nullable', 'integer', Rule::exists('scam_registration_amounts', 'id')],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'scam_type_id' => 'scam type',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {

        $scam = $this->route('scam');
        $type = $this->input('type');
        $statusId = $this->input('status_id');

        $status = ScamStatus::find($statusId, ['id', 'is_data_update_required', 'is_scam_type_update_required']);

        // Customer details validation
        $validator->sometimes(
            attribute: ['first_name', 'email', 'scam_amount'],
            rules: 'required',
            callback: function () use ($scam, $type, $status): bool {

                if (! $type || ! $scam || ! $status || $type === ScamStatusType::SALES->value) {
                    return false;
                }

                $isDraftingPending = $type === ScamStatusType::DRAFTING->value && is_null($scam->drafting_status_id);
                $isSalesPending = $type === ScamStatusType::SALES->value && is_null($scam->sales_status_id);

                return $status->is_data_update_required && ($isDraftingPending || $isSalesPending);
            }
        );

        // Scam details validation
        $validator->sometimes(
            attribute: ['scam_type_id'],
            rules: 'required',
            callback: function () use ($scam, $type, $status): bool {

                if (! $type || ! $scam || ! $status) {
                    return false;
                }

                $isDraftingPending = $type === ScamStatusType::DRAFTING->value && is_null($scam->drafting_status_id);
                $isSalesPending = $type === ScamStatusType::SALES->value && is_null($scam->sales_status_id);

                return $status->is_scam_type_update_required && ($isDraftingPending || $isSalesPending);
            }
        );

        // File Validation
        $validator->after(function ($validator): void {
            if ($statusId = $this->input('status_id')) {
                // Fetch the is_file_required value from the database
                $isFileRequired = DB::table('scam_statuses')
                    ->where('id', $statusId)
                    ->value('is_file_required');

                if ($isFileRequired === 1) {
                    // Make 'files' required
                    $validator->addRules([
                        'files' => ['required', 'array'],
                    ]);
                } elseif ($isFileRequired === 0) {
                    // Prohibit 'files' if not required
                    $validator->addRules([
                        'files' => ['prohibited'],
                    ]);
                }
            }
        });
    }
}
