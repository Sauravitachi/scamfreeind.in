<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\VltradingLead;
use App\Models\ScamSource;
use Illuminate\Http\JsonResponse;

class VltradingController extends Controller
{
    /**
     * Store a newly created vltrading lead in storage.
     */
    public function store(ScamLeadRequest $request): JsonResponse
    {
        // 1. Set default scam source to 'website' if not provided
        $scamSource = ScamSource::firstOrCreate(
            ['slug' => 'website'],
            ['title' => 'Website']
        );

        // 2. Prepare attributes
        $data = $request->validated();
        $data['scam_source_id'] = $request->input('scam_source_id') ?? $scamSource->id;

        // 3. Create the lead
        $vltradingLead = VltradingLead::create($data);

        return response()->json([
            'success' => true,
            'message' => 'VLTrading consultation requested successfully.',
            'data' => $vltradingLead
        ], 201);
    }
}
