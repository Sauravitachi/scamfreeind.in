<?php

namespace App\Notifications;

use App\Models\Scam;
use App\Models\User;
use App\Services\ScamService;
use App\Utilities\Structure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CaseAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Scam $scam
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $title = 'You have been assigned with a new case!';
        $message = ScamService::getInstance()->getScamTitle($this->scam);

        return Structure::notificationData(
            title: $title,
            message: $message
        );
    }
}
