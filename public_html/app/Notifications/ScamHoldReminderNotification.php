<?php

namespace App\Notifications;

use App\Models\Scam;
use App\Utilities\Structure;
use Illuminate\Notifications\Notification;

class ScamHoldReminderNotification extends Notification
{
    public function __construct(
        public Scam $scam
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $data = Structure::notificationData(
            title: '⚠️ Hold Scam Will Be Unassigned Soon',
            message: sprintf(
                'Scam #%s is on HOLD and will be automatically unassigned in 2 days. Please take action to avoid losing this lead.',
                $this->scam->customer->phone_number
            ),
            link: route('admin.scams.show', $this->scam->id)
        );

        $data['type'] = 'warning';
        $data['phone_number'] = $this->scam->customer->phone_number;
        $data['track_id'] = $this->scam->track_id;

        return $data;
    }
}
