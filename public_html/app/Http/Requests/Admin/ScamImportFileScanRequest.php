<?php

namespace App\Http\Requests\Admin;

use App\Services\ScamService;
use Illuminate\Support\Arr;

class ScamImportFileScanRequest extends \App\Foundation\FormRequest
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
                'extensions:'.Arr::join(ScamService::ALLOWED_FILE_EXTENSIONS_FOR_IMPORT, ','),
                'max:10240', // Max Size : 10MB
            ],
            'unique_phone_number' => ['nullable', 'boolean'],
            'unique_scam_type' => ['nullable', 'boolean'],
            'unique_scam_amount' => ['nullable', 'boolean'],
        ];
    }
}
