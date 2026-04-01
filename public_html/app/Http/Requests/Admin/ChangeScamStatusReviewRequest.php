<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScamStatusReview;
use App\Enums\ScamStatusType;
use Illuminate\Validation\Rule;

class ChangeScamStatusReviewRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ScamStatusType::array())],
            'review' => ['required', 'string', Rule::in(ScamStatusReview::APPROVED, ScamStatusReview::REJECTED)],
            'review_remark' => ['string', 'min:25', 'max:2000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->sometimes('review_remark', 'required', function ($input): bool {
            return $input->review !== ScamStatusReview::APPROVED->value;
        });
    }
}
