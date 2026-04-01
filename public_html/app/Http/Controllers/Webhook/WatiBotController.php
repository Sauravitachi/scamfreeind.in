<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Requests\Admin\ScamLeadRequest;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WatiBotController extends \App\Foundation\Controller
{
    /**
     * Constructor for WatiBotController
     */
    public function __construct(
        protected ScamLeadService $scamLeadService,
        protected ResponseService $responseService
    ) {}

    public function registerLead(ScamLeadRequest $request): JsonResponse
    {
        $request->merge(['source' => 'whatsapp']);

        $this->scamLeadService->registerLeadFromExternalSource($request);

        return $this->responseService->json(success: true, data: 'Lead registered succesfuly!');
    }

    public function chat(Request $request)
    {
        Log::info('Wati Chat Request Input:', $request->all());
    }
}
