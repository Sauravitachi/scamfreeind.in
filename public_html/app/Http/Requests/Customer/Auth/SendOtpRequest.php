<?php

namespace App\Http\Requests\Customer\Auth;

use App\Models\Customer;
use Illuminate\Validation\Validator;

class SendOtpRequest extends \App\Foundation\FormRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'digits:10'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $phone = $this->input('phone_number');

            $customer = Customer::where('phone_number', $phone)->where('dial_code', '91')->first(['id']);

            if (! $customer) {
                $validator->errors()->add('phone_number', 'Customer not found.');

                return;
            }

            $registeredStatusId = 6;

            $hasRegisteredScam = $customer->scams()
                ->where('is_duplicate', false)
                ->where('sales_status_id', $registeredStatusId)
                ->exists();

            if (! $hasRegisteredScam) {
                $validator->errors()->add('phone_number', 'Customer not found.');
            }
        });
    }
}
