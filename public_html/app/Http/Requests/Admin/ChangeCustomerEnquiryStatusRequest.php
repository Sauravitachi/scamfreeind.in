<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerEnquiryStatusType;
use App\Models\CustomerEnquiryStatus;
use Illuminate\Validation\Rule;

class ChangeCustomerEnquiryStatusRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status_id' => ['nullable', 'integer', Rule::exists('customer_enquiry_statuses', 'id')],
            'type' => ['required', 'string', Rule::in(CustomerEnquiryStatusType::array())],
            'remark' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance after the default validation rules are applied.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->sometimes('remark', 'required', function () {

            $statusId = $this->input('status_id');

            if ($statusId) {
                $status = CustomerEnquiryStatus::find($statusId, ['is_remark_required']);

                return $status && $status->is_remark_required;
            }

            return false;
        });
    }
}
