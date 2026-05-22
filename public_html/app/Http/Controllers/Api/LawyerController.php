<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\LawyerLead;
use App\Models\ScamSource;
use App\Models\ProblemType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LawyerController extends Controller
{
    /**
     * Display a listing of legal problem types.
     */
    public function index(Request $request): JsonResponse
    {
        $problemTypes = ProblemType::orderBy('title')->get(['id', 'title', 'slug']);

        return response()->json([
            'success' => true,
            'data' => $problemTypes
        ]);
    }

    /**
     * Store a newly created lawyer lead in storage.
     */
    public function create(ScamLeadRequest $request): JsonResponse
    {
        // 1. Resolve or dynamically create the ProblemType based on the problem_type string
        $problemTypeId = null;
        $problemType = $request->input('problem_type') ?? $request->input('problemType');

        if ($problemType) {
            $scamType = ProblemType::firstOrCreate(
                ['slug' => Str::slug($problemType, '_')],
                ['title' => $problemType]
            );
            $problemTypeId = $scamType->id;
        } else {
            // Fallback: Parse problem type from message/description if present
            $desc = $request->input('customer_description') ?? $request->input('message');
            if ($desc && preg_match('/legal issue:\s*([^\n\r\t]+)/i', $desc, $matches)) {
                $parsedType = trim($matches[1]);
                $scamType = ProblemType::firstOrCreate(
                    ['slug' => Str::slug($parsedType, '_')],
                    ['title' => $parsedType]
                );
                $problemTypeId = $scamType->id;
            }
        }

        // 2. Set default scam source to 'website'
        $scamSource = ScamSource::firstOrCreate(
            ['slug' => 'website'],
            ['title' => 'Website']
        );

        // 3. Prepare lawyer lead attributes
        $data = $request->validated();
        if ($problemTypeId) {
            $data['problem_type_id'] = $problemTypeId;
        }
        $data['scam_source_id'] = $scamSource->id;
        $data['name'] = $request->input('name') ?? 'Lawyer Booking';

        // 4. Create the lawyer lead in the isolated table
        $lawyerLead = LawyerLead::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lawyer consultation requested successfully.',
            'data' => $lawyerLead
        ], 201);
    }
}

