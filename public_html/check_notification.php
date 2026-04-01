<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$notification = DB::table('notifications')->latest()->first();

if ($notification) {
    echo "Latest Notification:\n";
    print_r(json_decode($notification->data, true));
    echo "To User ID: " . $notification->notifiable_id . "\n";
    echo "Type: " . $notification->type . "\n";
    echo "Created At: " . $notification->created_at . "\n";
} else {
    echo "No notifications found in the database.\n";
}
