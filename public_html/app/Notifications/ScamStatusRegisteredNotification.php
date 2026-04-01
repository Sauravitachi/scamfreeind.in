<?php

namespace App\Notifications;

use App\Models\ScamRegistration;
use App\Utilities\Structure;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
// use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScamStatusRegisteredNotification extends Notification
{

    public function __construct(
        public ScamRegistration $registration
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
{
    $user   = optional($this->registration->causer);
    // Ensure we are getting the actual numeric amount from the related model
    $amount = (float) ($this->registration->scamRegistrationAmount?->amount ?? 0);

    $data = Structure::notificationData(
        title: 'Scam Registered Successfully',
        message: sprintf(
            '%s registered scam amount %s',
            $user->name ?? 'A user',
            $amount > 0 ? '₹' . number_format($amount, 2) : 'N/A'
        )
    );

    if ($amount > 2999) {
        $data['type'] = 'fireworks';
    } else {
        $data['type'] = 'normal';
    }

    $data['scam_id'] = $this->registration->scam_id;
    $data['registration_id'] = $this->registration->id;

    return $data;
}
}
