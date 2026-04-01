<?php

namespace App\Foundation;

use App\Utilities\Validation;
use Illuminate\Foundation\Http\FormRequest as HttpFormRequest;

abstract class FormRequest extends HttpFormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Checks if all the specified fields in the request are filled (not empty).
     *
     * This method iterates through an array of field names and verifies that each field
     * is present and not empty in the request. If any field is missing or empty, the method
     * returns `false`. Otherwise, it returns `true`.
     *
     * @param  array<string>  $fields  An array of strings representing the field names to check.
     * @return bool Returns `true` if all fields are filled, otherwise `false`.
     */
    protected function areFilled(array $fields): bool
    {
        foreach ($fields as $field) {
            if (! $this->filled($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules for a valid country.
     *
     * @return array Validation rules for the country input.
     */
    protected function countryValidationRules(): string
    {
        return Validation::countryValidationRules();
    }
}
