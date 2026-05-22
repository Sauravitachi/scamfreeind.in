<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChatNotification extends Notification
{
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $senderName = $this->message->sender->name ?? ($this->message->sender->username ?? 'Someone');
        $body = $this->message->type === 'text' ? $this->message->body : 'sent a ' . $this->message->type;

        return [
            'title' => 'New Chat Transmission',
            'message' => $senderName . ': ' . (strlen($body) > 50 ? substr($body, 0, 47) . '...' : $body),
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $senderName,
            'body' => $body,
            'type' => $this->message->type,
            'link' => route('admin.chat', ['conversation' => $this->message->conversation_id]),
        ];
    }


    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $senderName = $this->message->sender->name ?? ($this->message->sender->username ?? 'Someone');
        $body = $this->message->type === 'text' ? $this->message->body : 'sent a ' . $this->message->type;

        return new BroadcastMessage([
            'title' => 'New Chat Transmission',
            'message' => $senderName . ': ' . (strlen($body) > 50 ? substr($body, 0, 47) . '...' : $body),
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_name' => $senderName,
            'body' => $body,
        ]);
    }
}


