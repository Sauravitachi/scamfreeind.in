<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HelperService extends Service
{
    public function requestNotificationMarkAsRead(Request $request): void
    {
        if ($source = $request->get('source')) {

            if (str_starts_with($source, 'notification-')) {
                $notificationId = str_replace('notification-', '', $request->source);
                $request->user()?->unreadNotifications()->where('id', $notificationId)->update(['read_at' => now()]);
            }

        }

    }

    public function getOfficeTiming(): ?array
    {
        $settings = setting(['office_start_time', 'office_end_time'])->pluck('value', 'key');

        if ($settings->count() !== 2) {
            return null;
        }

        $startTime = Carbon::createFromTimeString($settings['office_start_time']);
        $endTime = Carbon::createFromTimeString($settings['office_end_time']);

        // If end time is less than start time, it means it crosses midnight
        if ($endTime->lessThan($startTime)) {
            // So we add 1 day to end time
            $endTime->addDay();
        }

        return [$startTime, $endTime];
    }

    public function getLatestLaravelVersion(): ?string
    {
        return Cache::remember('latest_laravel_version_available', now()->addDay(), function () {
            try {
                $response = Http::get('https://repo.packagist.org/p2/laravel/framework.json');

                if ($response->ok()) {
                    $data = $response->json();

                    $versions = $data['packages']['laravel/framework'] ?? [];

                    // Filter stable versions only
                    $stableVersions = array_filter($versions, function ($version) {
                        return ! str_contains($version['version'], 'dev') &&
                            ! str_contains($version['version'], 'alpha') &&
                            ! str_contains($version['version'], 'beta') &&
                            ! str_contains($version['version'], 'RC');
                    });

                    // Get the latest version (sorted descending by version_normalized)
                    usort($stableVersions, fn ($a, $b) => version_compare($b['version_normalized'], $a['version_normalized']));

                    return $stableVersions[0]['version'] ?? null;
                }

            } catch (\Throwable $e) {
                return null;
            }

            return null;
        });
    }
}
