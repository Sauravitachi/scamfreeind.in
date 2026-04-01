<?php

namespace App\Http\Requests\Admin;

use App\Constants\Permission;
use Illuminate\Http\Request;

class ChangeAccountPasswordRequest extends \App\Foundation\FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        if ($this->filled('super') && request()->user()?->cannot(Permission::CHANGE_ALL_USERS_PASSWORD)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        if (! $this->filled('super')) {
            $rules['current_password'] = ['required', 'string', 'current_password:api']; // must be self user logged in
        }

        $rules['new_password'] = ['required', 'string', 'min:8'];
        $rules['confirm_new_password'] = ['required', 'string', 'same:new_password'];

        return $rules;
    }
}
