<?php

namespace App\Observers;

use App\Models\ScamRegistration;
use App\Models\User;
use App\Notifications\ScamStatusRegisteredNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class ScamRegistrationObserver
{
    /**
     * Handle the ScamRegistration "created" event.
     */
    // Notification logic moved to ChangeStatus action after transaction commit.
}
