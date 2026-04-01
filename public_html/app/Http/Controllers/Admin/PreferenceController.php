<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\DTO\Toast;
use App\Http\Requests\Admin\UserPreferenceRequest;
use App\Services\PreferenceService;
use App\Services\ResponseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class PreferenceController extends \App\Foundation\Controller implements HasMiddleware
{
    /**
     * Constructor for PreferenceController
     */
    public function __construct(
        protected PreferenceService $service,
        protected ResponseService $responseService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::USER_PREFERENCES, only: ['index', 'update']),
        ];
    }

    public function index(Request $request): View
    {
        $preferences = $request->user()->preferencesMap;

        return view('admin.preferences.index', compact('preferences'));
    }

    public function update(UserPreferenceRequest $request): RedirectResponse
    {
        $this->service->update($request->user(), $request);

        return redirect()->back()->with('toast', new Toast(type: 'success', message: 'Preferences Saved!'));
    }
}
