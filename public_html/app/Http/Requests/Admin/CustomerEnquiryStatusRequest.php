<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerEnquiryStatusType;
use Illuminate\Validation\Rule;

class CustomerEnquiryStatusRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $scamStatus = $this->route('customer_enquiry_status');

        return [
            'slug' => [
                'required',
                'alpha_dash',
                'max:250',
                Rule::unique('customer_enquiry_statuses', 'slug')->ignore($scamStatus),
            ],
            'title' => ['required', 'string', 'max:250'],
            'type' => ['required', 'string', Rule::in(CustomerEnquiryStatusType::array())],
            'is_remark_required' => ['nullable', 'boolean'],
            'consider_resolved' => ['nullable', 'boolean'],
            'unassign_scam' => ['nullable'],
            'unassign_scam_in_days' => ['required_with:unassign_scam', 'integer', 'gte:0', 'max:999'],
        ];
    }
}
