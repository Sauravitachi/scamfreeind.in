<?php

namespace App\Traits\Models;

use App\Contracts\WhatsappProfileContract;
use App\Enums\WatiTemplateName;
use App\Libraries\WatiWhatsapp;
use App\Traits\ModelSupport;
use RuntimeException;

/**
 * @property string|null $phone_number
 * @property string|null $dial_code
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasPhoneNumber
{
    use ModelSupport;

    public function getFullPhoneNumberAttribute(): string
    {
        if (! $this->hasAllAttributes('phone_number', 'dial_code')) {
            $this->refresh();
        }
        $phoneNumber = $this->phone_number;
        if ($this->dial_code) {
            $phoneNumber = "+$this->dial_code $phoneNumber";
        }

        return $phoneNumber;
    }

    public function getWhatsappPhoneNumberAttribute(): string
    {
        if (! $this->hasAllAttributes('phone_number', 'dial_code')) {
            $this->refresh();
        }
        $phoneNumber = $this->phone_number;
        if ($this->dial_code) {
            $phoneNumber = $this->dial_code.$phoneNumber;
        }

        return $phoneNumber;
    }

    public function sendWhatsappMessage(WatiTemplateName $templateName, array $parameters = []): array
    {
        if (! $this instanceof WhatsappProfileContract) {
            throw new RuntimeException(get_class($this).' must implement WhatsappProfileContract to send WhatsApp messages.');
        }

        return (new WatiWhatsapp)->send(
            phone: $this->whatsappPhoneNumber,
            templateName: $templateName,
            parameters: $parameters,
            recipient: $this
        );
    }
}
