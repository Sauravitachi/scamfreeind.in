<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsappMessageLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'whatsapp_number',
        'template_name',
        'broadcast_name',
        'payload',
        'response',
        'response_status_code',
        'recipient_id',
        'recipient_type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function getRecipientEntityTypeAttribute(): ?string
    {
        if ($this->recipient_type) {
            return str_replace('App\\Models\\', '', $this->recipient_type);
        }

        return null;
    }

    /**
     * Get the parent model (user, customer, etc.) that the message log belongs to.
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }
}
