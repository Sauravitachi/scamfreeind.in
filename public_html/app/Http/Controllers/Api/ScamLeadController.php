<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\ScamLead;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;

class ScamLeadController extends Controller
{
    public function __construct(
        protected ScamLeadService $scamLeadService
    ) {}

    /**
     * Store a newly created scam lead in storage.
     */
    public function store(ScamLeadRequest $request): JsonResponse
    {
        $scamLead = $this->scamLeadService->create($request);

        return response()->json([
            'message' => 'Scam lead reported successfully.',
            'data' => $scamLead,
        ], 201);
    }
}
