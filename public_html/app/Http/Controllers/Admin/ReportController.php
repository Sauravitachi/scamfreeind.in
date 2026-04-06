<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Services\ReportService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends \App\Foundation\Controller
{
    /**
     * Constructor for ReportController
     */
    public function __construct(
        protected ReportService $service,
        protected ResponseService $responseService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::REPORT_USER_SCAM_STATUS, only: ['userCaseStatusReport']),
            permit(Permission::REPORT_SCAM_STATUS_TRANSITION, only: ['scamStatusTransitionReport']),
        ];
    }

    public function userCaseStatusReport(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {

            return $this->responseService->json(
                success: true,
                data: $this->service->userCaseReport($request)
            );

        }

        return view('admin.reports.user-case-report');
    }

    public function scamStatusTransitionReport(Request $request): View
    {
        $salesStatuses = \App\Models\ScamStatus::where('type', 'sales')->orderBy('title')->get();
        $draftingStatuses = \App\Models\ScamStatus::where('type', 'drafting')->orderBy('title')->get();
        
        $user = auth()->user();
        $query = \App\Models\User::query();

        // 1. Define administrative roles that can see everyone
        $adminRoles = ['Admin', 'Super Admin', 'Manager', 'Sub Admin', 'Product Head', 'MIS', 'Auditor', 'Tech Team'];
        
        // 2. Define restricted roles (Sales, Drafting, Service) who should only see their own reports
        $restrictedRoles = ['Sales Executive', 'Drafting Executive', 'Service Executive', 'Team Leader - TL'];

        // 3. Apply filtering based on user role
        if ($user && $user->hasAnyRole($restrictedRoles) && !$user->hasAnyRole($adminRoles)) {
            // Limited to self
            $query->where('id', $user->id);
        } else {
            // Can see all relevant staff roles
            $query->whereHas('roles', function($q) use ($adminRoles, $restrictedRoles) {
                $q->whereIn('name', array_merge($adminRoles, $restrictedRoles));
            });
        }

        $users = $query->orderBy('name')->get();

        return view('admin.reports.scam-status-transition-report', compact('salesStatuses', 'draftingStatuses', 'users'));
    }
}
