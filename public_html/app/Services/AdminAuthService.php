<?php

namespace App\Services;

use App\Constants\Permission;
use App\Constants\Setting;
use App\DTO\LoginPermit;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable as AuthUser;

class AdminAuthService extends Service
{
    public function canLogin(User|AuthUser $admin): LoginPermit
    {
        if ($admin->cannot(Permission::ADMIN_PANEL->value)) {
            return new LoginPermit(canLogin: false, message: 'Account is not authorized to visit the dashboard.');
        }

        if (! setting(Setting::PANEL_LOGIN, false)) {
            if (! $admin->hasPermissionTo(Permission::BYPASS_DISABLED_LOGIN)) {
                return new LoginPermit(canLogin: false, message: 'Panel login is disabled right now. Try again later!');
            }
        }

        if (setting(Setting::IP_LOGIN, false)) {
            if (! $admin->hasPermissionTo(Permission::BYPASS_DISABLED_LOGIN)) {
                $allowedIps = setting(Setting::ALLOWED_IPS, '');
                $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
                $currentIp = request()->ip();

                if (! in_array($currentIp, $allowedIps)) {
                    return new LoginPermit(canLogin: false, message: "IP restricted! Your IP ($currentIp) is not allowed.");
                }
            }
        }

        // check of active/inactive status
        if (! $admin->status) {
            return new LoginPermit(canLogin: false, message: 'Inactive Account! Please contact administration.');
        }

        return new LoginPermit(canLogin: true);
    }
}
