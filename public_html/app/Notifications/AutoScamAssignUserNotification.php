<?php

namespace App\Notifications;

use App\Utilities\Structure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AutoScamAssignUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected int $assignedCasesCount
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
    public function toArray(object $notifiable): array
    {
        $title = 'Reward Cases Assigned 🎉';

        return Structure::notificationData(
            title: $title,
            message: "Congratulations! You have been rewarded with {$this->assignedCasesCount} new case(s)!",
        );
    }
}
