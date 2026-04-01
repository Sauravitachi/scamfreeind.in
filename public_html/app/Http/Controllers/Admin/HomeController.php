<?php

namespace App\Http\Controllers\Admin;

use App\Services\ActivityLogService;
use App\Services\DashboardService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AppUiData;
use Illuminate\View\View;

class HomeController extends \App\Foundation\Controller
{
    /**
     * Constructor for HomeController
     */
    public function __construct(
        protected DashboardService $service,
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService
    ) {}

    public function index(Request $request): JsonResponse|View
    {

        if ($request->ajax()) {

            return $this->responseService->json(
                success: true,
                data: $this->service->ajax($request)
            );

        }

        $this->activityLogService->visited('dashboard');

        return view('admin.home.index', $this->service->viewData());
    }

    public function getVideoSectionData(): JsonResponse
    {
        $data = AppUiData::getVideoSectionData();
        
        return $this->responseService->json(
            success: true,
            data: $data ? $data->getData() : null
        );
    }    
    
}
