<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Services\HelperService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class RandomScamAssignRequest extends \App\Foundation\FormRequest
{
    public function rules(): array
    {
        return [
            'filtered_scams_count' => ['required', 'integer', 'gt:0'],
            'count' => ['required', 'integer', 'min:1', 'max:300'],
            'assignees' => ['required', 'array', 'min:1', 'max:100'],
            'scam_amount_lb' => ['nullable', 'integer', 'gte:0'],
            'scam_amount_ub' => ['nullable', 'integer', 'gte:0'],
            'include_null_amount' => ['nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'count' => 'number of items',
            'assignees' => 'assignees',
            'assignees.*' => 'assignees',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $assignees = $this->input('assignees', []);

            if (! empty($assignees)) {
                $officeTimings = once(fn () => HelperService::getInstance()->getOfficeTiming());

                $existingAssigneesCount = User::whereIn('id', $assignees)
                    ->whereSales()
                    ->where('status', true)
                    ->when($officeTimings !== null, function (Builder $q) use ($officeTimings) {
                        $q->whereHas('lastActivity', function (Builder $q) use ($officeTimings) {
                            $q->whereBetween('created_at', $officeTimings);
                        });
                    })
                    ->count();

                if ($existingAssigneesCount !== count($assignees)) {
                    $validator->errors()->add('assignees', 'One or more selected assignees do not exist.');
                }
            }
        });

        $validator->after(function ($validator) {
            $lb = $this->input('scam_amount_lb');
            $ub = $this->input('scam_amount_ub');

            if (! is_null($lb) && ! is_null($ub) && $lb > $ub) {
                $validator->errors()->add('scam_amount_lb', 'The lower bound must be less than or equal to the upper bound.');
                $validator->errors()->add('scam_amount_ub', 'The upper bound must be greater than or equal to the lower bound.');
            }
        });
    }
}
