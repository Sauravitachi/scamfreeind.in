<?php

namespace App\Notifications;

use App\Models\Escalation;
use App\Models\User;
use App\Services\EscalationService;
use App\Utilities\Structure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CaseEscalatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Escalation $escalation
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

        $title = 'Your case escalated!';
        $message = EscalationService::getInstance()->getEscalationTitle($this->escalation);

        return Structure::notificationData(
            title: $title,
            message: $message,
            link: route('admin.escalations.show', $this->escalation)
        );
    }
}
