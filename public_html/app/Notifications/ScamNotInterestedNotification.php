<?php

namespace App\Notifications;

use App\Models\Scam;
use App\Utilities\Structure;
use Illuminate\Notifications\Notification;

class ScamNotInterestedNotification extends Notification
{
    public function __construct(
        public Scam $scam,
        public $causer
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $data = Structure::notificationData(
            title: 'Scam Marked as Not Interested',
            message: sprintf(
                '%s marked scam #%s as "Not Interested"',
                $this->causer->name ?? 'A user',
                $this->scam->customer->phone_number
            ),
            link: route('admin.scams.show', $this->scam->id)
        );

        $data['type'] = 'normal';
        $data['phone_number'] = $this->scam->customer->phone_number;
        $data['track_id'] = $this->scam->track_id;

        return $data;
    }
}
