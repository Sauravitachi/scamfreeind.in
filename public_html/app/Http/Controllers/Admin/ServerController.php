<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Services\HelperService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServerController extends \App\Foundation\Controller implements HasMiddleware
{
    public function __construct(
        protected HelperService $helperService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::PHPINFO, only: ['phpinfo']),
            permit(Permission::LARAVEL_INFO, only: ['laravelInfo']),
        ];
    }

    public function phpinfo()
    {
        echo phpinfo();
    }

    public function laravelInfo(): View
    {
        $latestLaravelVersion = $this->helperService->getLatestLaravelVersion();

        $info = [
            'Laravel Version' => app()->version(),
            'Laravel Version (Latest Available)' => $latestLaravelVersion ?? 'Unavailable',
            'PHP Version' => PHP_VERSION,
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'Cache Driver' => config('cache.default'),
            'Session Driver' => config('session.driver'),
            'Queue Driver' => config('queue.default'),
            'Database Connection' => config('database.default'),
            'Database Version' => DB::select('select version() as version')[0]->version ?? 'Unknown',
            'Timezone' => config('app.timezone'),
            'Locale' => config('app.locale'),
        ];

        return view('admin.server.laravel-info', compact('info'));
    }
}
