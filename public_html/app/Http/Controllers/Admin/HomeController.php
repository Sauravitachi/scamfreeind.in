<?php

namespace App\Http\Controllers\Admin;

use App\Services\ActivityLogService;
use App\Services\DashboardService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\AppUiData;
use Illuminate\Support\Facades\Cache;
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
        $page = request()->get('page', 1);
        $cacheKey = "api_video_section_data_page_{$page}";

        $data = Cache::rememberForever($cacheKey, function () {
            $uiData = AppUiData::getVideoSectionData();
            return $uiData ? $uiData->getData() : null;
        });

        return $this->responseService->json(
            success: true,
            data: $data
        );
    }


    public function getExpertSectionData(): JsonResponse
    {
        $data = Cache::rememberForever('api_expert_section_data', function () {
            $data = AppUiData::getExpertSectionData();
            return $data ? $data->getData() : null;
        });
        
        return $this->responseService->json(
            success: true,
            data: $data
        );
    }    
    
}
