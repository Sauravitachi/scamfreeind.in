<?php

namespace App\Http\Requests\Admin;

class UploadScamFilesRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,bmp,tiff,webp,xlsx,xls,csv,docx,pdf'],
            'messages' => ['nullable', 'array', 'min:1'],
            'messages.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'files.*' => 'file',
            'messages.*' => 'message',
        ];
    }
}
