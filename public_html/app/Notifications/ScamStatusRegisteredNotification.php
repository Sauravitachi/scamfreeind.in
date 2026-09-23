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

    $defaultQuotes = [
        "Believe you can and you're halfway there.",
        "Success is not final, failure is not fatal: it is the courage to continue that counts.",
        "The only way to do great work is to love what you do.",
        "Opportunities don't happen. You create them.",
        "Don't count the days, make the days count.",
        "Action is the foundational key to all success.",
        "Dream big and dare to fail.",
        "Focus on being productive instead of busy.",
        "Your limitation—it's only your imagination.",
        "Push yourself, because no one else is going to do it for you."
    ];
    $randomQuote = $defaultQuotes[array_rand($defaultQuotes)];

    $data['scam_id'] = $this->registration->scam_id;
    $data['registration_id'] = $this->registration->id;
    $data['causer_name'] = $user->name ?? 'A user';
    $data['causer_avatar'] = $user->profile_avatar ?? null;
    $data['causer_quote'] = !empty($user->quote) ? $user->quote : $randomQuote;

    return $data;
}
}
