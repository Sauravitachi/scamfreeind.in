<?php

namespace App\Http\Requests\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Foundation\FormRequest;

class BlogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $blogId = $this->route('blog');

        return [
            'title' => [
                'required',
                'string',
                'max:250',
                Rule::unique('blogs', 'title')->ignore($blogId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:250',
                'alpha_dash',
                Rule::unique('blogs', 'slug')->ignore($blogId),
            ],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5024'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:250'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
