<?php

namespace App\Http\Requests\Admin;

class UserForceReleaseFreezeRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'freeze_disabled_until_hours' => ['required', 'integer', 'min:1', 'max:999999'],
        ];
    }
}
