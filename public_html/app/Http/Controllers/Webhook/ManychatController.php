<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Requests\Admin\ScamLeadRequest;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;

class ManychatController extends \App\Foundation\Controller
{
    /**
     * Constructor for ManychatController
     */
    public function __construct(
        protected ScamLeadService $scamLeadService,
        protected ResponseService $responseService
    ) {}

    public function registerInstagramLead(ScamLeadRequest $request): JsonResponse
    {
        $request->merge(['source' => 'instagram']);

        $this->scamLeadService->registerLeadFromExternalSource($request);

        return $this->responseService->json(success: true, data: 'Lead registered succesfuly!');
    }

    public function registerFacebookLead(ScamLeadRequest $request): JsonResponse
    {
        $request->merge(['source' => 'facebook']);

        $this->scamLeadService->registerLeadFromExternalSource($request);

        return $this->responseService->json(success: true, data: 'Lead registered succesfuly!');
    }
}
