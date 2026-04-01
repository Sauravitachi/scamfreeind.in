<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Yajra\DataTables\EloquentDataTable;

class NotificationService extends Service
{
    public const  NOTIFICATION_DROPDOWN_LIMIT = 5;

    public const  NOTIFICATION_DROPDOWN_REFRESH_INTERVAL_SECONDS = 10;

    public function dataTable(Request $request): EloquentDataTable
    {
        $user = $request->user();

        $query = DatabaseNotification::query();

        $query->select(['id', 'data', 'created_at']);

        $query->where('notifiable_type', \App\Models\User::class)->where('notifiable_id', $user->id);

        if (($type = $request->get('type')) && in_array($type, ['read', 'unread'])) {
            $query->{$type === 'read' ? 'whereNotNull' : 'whereNull'}('read_at');
        }

        $table = datatables()->eloquent($query);

        $table->editColumn('created_at', fn (DatabaseNotification $n) => format_date($n->created_at));
        $table->addColumn('notification_link', fn (DatabaseNotification $n) => $this->getNotificationLink($n));

        return $table;
    }

    public function getUserNotificationDropdownData(Request $request): array
    {
        $count = $request->user()->unreadNotifications()->count();
        $notifications = $request->user()->unreadNotifications()->latest()->limit(self::NOTIFICATION_DROPDOWN_LIMIT)->get(['id', 'data']);

        return compact('count', 'notifications');
    }

    public function getNotificationLink(DatabaseNotification $notification): ?string
    {
        $link = $notification->data['link'] ?? null;

        return $link ? "{$notification->data['link']}?source=notification-{$notification->id}" : null;
    }
}
