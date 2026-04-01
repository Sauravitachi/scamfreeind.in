<?php

namespace App\Http\Requests\Admin;

use App\DTO\Toast;
use App\Enums\ScamStatusFieldType;
use App\Models\ScamStatus;
use App\Models\ScamStatusUpdateField;
use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UpdateStatusDataRequest extends \App\Foundation\FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /**
         * @var \App\Models\Scam $scam
         * @var \App\Models\ScamStatus $scamStatus
         */
        $scam = $this->route('scam');
        $scamStatus = $this->route('scam_status');

        if (! $scam || ! $scamStatus) {
            throw new RuntimeException('Required route data is not given or accessible!');
        }

        if ($scamStatus->statusUpdateFields->isEmpty()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'No update fields available for this status.',
            ], 422));
        }

        $this->merge(['scam_status' => $scamStatus]);

        return $scamStatus->statusUpdateFields->mapWithKeys(function (ScamStatusUpdateField $updateField) use ($scam) {
            return [
                $updateField->status_field_type->name() => $updateField->status_field_type->rules(statusUpdateField: $updateField, scam: $scam),
            ];
        })->toArray();
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        $toastMessageFields = [ScamStatusFieldType::FILE_UPLOAD, ScamStatusFieldType::REGISTRATION_AMOUNT];

        foreach ($errors as $field => $messages) {
            // Check using your own logic – adjust this if you use a different field name logic
            $fieldType = $this->getFieldTypeByFieldName($field);
            if (in_array($fieldType, $toastMessageFields) && isset($messages[0])) {
                $response = ResponseService::getInstance()->json(success: false, toast: new Toast(type: 'error', message: $messages[0]));
                throw new HttpResponseException($response);
            }
        }

        // Fallback to normal error response
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $errors,
        ], 422));
    }

    protected function getFieldTypeByFieldName(string $fieldName): ?ScamStatusFieldType
    {
        /** @var ScamStatus|null $scamStatus */
        $scamStatus = $this->route('scam_status');

        foreach ($scamStatus->statusUpdateFields as $updateField) {
            if ($updateField->status_field_type->name() === $fieldName) {
                return $updateField->status_field_type;
            }
        }

        return null;
    }
}
