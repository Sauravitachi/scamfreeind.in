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
}
