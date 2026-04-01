<?php

namespace App\Http\Requests\Admin;

use App\Constants\FileDirectory;
use Illuminate\Validation\Rule;

class FileUploadRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // Maximum size in kilobytes (10 MB = 10240 KB)
                'mimes:jpg,jpeg,png,gif,webp,bmp,tiff,avif,webp,xlsx,xls,csv,docx,pdf,ppt,pptx,txt,rtf,odt,csv',
            ],
            'file_directory' => ['required', 'string', Rule::in(FileDirectory::array())],
        ];
    }
}
