<?php

namespace App\Enums;

use App\Models\Scam;
use App\Models\ScamRegistrationAmount;
use App\Models\ScamStatusUpdateField;
use App\Models\ScamType;
use App\Traits\EnumSupport;
use App\Utilities\Html;
use Illuminate\Validation\Rule;
use Illuminate\View\ComponentAttributeBag;

enum ScamStatusFieldType: string
{
    use EnumSupport;

    case STATUS_REMARK = 'status_remark';
    case FIRST_NAME = 'first_name';
    case LAST_NAME = 'last_name';
    case EMAIL = 'email';
    case SCAM_AMOUNT = 'scam_amount';
    case REGISTRATION_AMOUNT = 'registration_amount';
    case SCAM_TYPE = 'scam_type';
    case FILE_UPLOAD = 'file_upload';
    case STATUS_NOTIFY_AT = 'status_notify_at';

    public function label(): string
    {
        return match ($this) {
            self::STATUS_REMARK => 'Status Remark',
            self::FIRST_NAME => 'First Name',
            self::LAST_NAME => 'Last Name',
            self::EMAIL => 'Email',
            self::SCAM_AMOUNT => 'Scam Amount',
            self::REGISTRATION_AMOUNT => 'Case Registration Amount',
            self::SCAM_TYPE => 'Scam Type',
            self::FILE_UPLOAD => 'File Upload',
            self::STATUS_NOTIFY_AT => 'Status Notify'
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::STATUS_REMARK => 'status_remark',
            self::FIRST_NAME => 'first_name',
            self::LAST_NAME => 'last_name',
            self::EMAIL => 'email',
            self::SCAM_AMOUNT => 'scam_amount',
            self::REGISTRATION_AMOUNT => 'registration_amount',
            self::SCAM_TYPE => 'scam_type_id',
            self::FILE_UPLOAD => 'files',
            self::STATUS_NOTIFY_AT => 'status_notify_at'
        };
    }

    public function columnClass(): string
    {
        return match ($this) {
            self::REGISTRATION_AMOUNT => 'col-12',
            self::FILE_UPLOAD => 'col-12',
            self::STATUS_REMARK => 'col-12',
            self::STATUS_NOTIFY_AT => 'col-12',
            default => 'col-lg-6'
        };
    }

    public function rules(?ScamStatusUpdateField $statusUpdateField = null, ?Scam $scam = null): array
    {
        return match ($this) {
            self::STATUS_REMARK => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'string',
                'max:1000',
            ],
            self::FIRST_NAME => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'string',
                'max:100',
            ],
            self::LAST_NAME => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'string',
                'max:100',
            ],
            self::EMAIL => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'string',
                'max:250',
                'email',
                Rule::unique('customers', 'email')->ignore($scam?->customer->email, 'email'),
            ],
            self::SCAM_AMOUNT => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'numeric',
                'min:1',
                'max:999999999999',
            ],
            self::REGISTRATION_AMOUNT => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'array',
                'min:1',
                'max:100',
            ],
            self::SCAM_TYPE => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'integer',
                Rule::exists('scam_types', 'id'),
            ],
            self::FILE_UPLOAD => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'array',
                'max:100',
            ],
            self::STATUS_NOTIFY_AT => [
                $statusUpdateField?->is_required ? 'required' : 'nullable',
                'date_format:Y-m-d H:i',
                'after:now',
            ],
        };
    }

    public function inputField(?ScamStatusUpdateField $statusUpdateField = null, ?Scam $scam = null)
    {
        return match ($this) {

            self::STATUS_REMARK => $this->view('components.admin.textarea', [
                'name' => $this->name(),
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Enter remarks',
            ]),

            self::FIRST_NAME => $this->view('components.admin.input', [
                'name' => $this->name(),
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Enter first name',
                'value' => $statusUpdateField->prefill_previous_value ? $scam?->customer->first_name : null,
            ]),

            self::LAST_NAME => $this->view('components.admin.input', [
                'name' => $this->name(),
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Enter last name',
                'value' => $statusUpdateField->prefill_previous_value ? $scam?->customer->last_name : null,
            ]),

            self::EMAIL => $this->view('components.admin.input', [
                'name' => $this->name(),
                'type' => 'email',
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Enter email address',
                'value' => $statusUpdateField->prefill_previous_value ? $scam?->customer->email : null,
            ]),

            self::SCAM_AMOUNT => $this->view('components.admin.input', [
                'name' => $this->name(),
                'type' => 'number',
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Enter scam amount',
                'value' => $statusUpdateField->prefill_previous_value ? $scam?->scam_amount : null,
            ]),

            self::REGISTRATION_AMOUNT => $this->view('components.admin.select', [
                'id' => 'status_update_registration_amount_select',
                'label' => $this->label(),
                'class' => 'select2',
                'options' => ScamRegistrationAmount::where('is_active', true)->pluck('title', 'id')->toArray(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Select Amount',
            ]).Html::div('status_update_registration_amount_selected_values mb-3'),

            self::SCAM_TYPE => $this->view('components.admin.select', [
                'name' => $this->name(),
                'label' => $this->label(),
                'class' => 'select2',
                'options' => ScamType::pluck('title', 'id')->toArray(),
                'required' => $statusUpdateField?->is_required,
                'placeholder' => 'Select',
                'selected' => $statusUpdateField->prefill_previous_value ? $scam?->scam_type_id : null,
            ]),

            self::FILE_UPLOAD => $this->view('components.admin.dropzone-uploader', [
                'name' => $this->name(),
                'label' => $this->label(),
                'required' => $statusUpdateField?->is_required,
            ]),

            self::STATUS_NOTIFY_AT => $this->view('components.admin.input', [
                'name' => $this->name(),
                'label' => $this->label(),
                'class' => 'datetime_picker',
                'required' => $statusUpdateField?->is_required,
            ]),

        };
    }

    private function view(string $view, array $data = [])
    {
        return view($view, [
            ...$data,
            'attributes' => new ComponentAttributeBag($data),
        ])->render();
    }
}
