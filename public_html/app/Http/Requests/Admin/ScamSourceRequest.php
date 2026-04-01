<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ScamSourceRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $scamSourseId = $this->route('scam_source');

        return [
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('scam_sources', 'slug')->ignore($scamSourseId),
            ],
            'title' => [
                'required',
                'string',
                Rule::unique('scam_sources', 'title')->ignore($scamSourseId),
            ],
            'indicator_color' => ['required', 'hex_color'],
        ];
    }
}
