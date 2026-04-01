<?php

namespace App\Contracts;

interface WhatsappProfileContract
{
    public function getWhatsappNameAttribute(): ?string;

    public function getFullPhoneNumberAttribute(): string;
}
