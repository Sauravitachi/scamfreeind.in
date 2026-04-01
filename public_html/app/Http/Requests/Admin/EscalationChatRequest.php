<?php

namespace App\Http\Requests\Admin;

class EscalationChatRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required_if:file,null', 'string', 'max:1000'],
            'file' => ['nullable', 'file', 'mimes:png,jpg,pdf,csv,xlsx,docx', 'max:10240'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'message.required_if' => 'The message field is required.',
            'message.string' => 'The message field is required.',
        ];
    }
}
