<?php

namespace App\Notifications;

use App\Models\MissedAutoScamAssignRecord;
use App\Utilities\Structure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MissedAutoScamAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected MissedAutoScamAssignRecord $missedAutoScamAssignRecord
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
        $title = 'Auto Case assign missed because of inefficient cases.';

        $user = $this->missedAutoScamAssignRecord->user()->first(['id', 'name', 'username']);

        return Structure::notificationData(
            title: $title,
            message: "Sales Member : {$user->nameWithUsername} - ({$this->missedAutoScamAssignRecord->new_cases_count}) reward cases missed (due to inefficiency of cases)",
        );
    }
}
