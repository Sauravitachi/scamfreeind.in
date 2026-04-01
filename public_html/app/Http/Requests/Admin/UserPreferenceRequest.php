<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UserPreferenceRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', Rule::in(['light', 'dark'])],
            'menu_layout' => ['required', 'string', Rule::in(['vertical', 'horizontal'])],
        ];
    }
}
