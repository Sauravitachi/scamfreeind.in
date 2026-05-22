<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AstrologerConsultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AstrologerConsultationController extends Controller
{
    /**
     * Store a newly created consultation request.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'dob' => 'nullable|string', // Changed to string to handle various formats from frontend
            'pob' => 'nullable|string|max:255',
            'tob' => 'nullable|string',
            'acharya_name' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Parse Date of Birth
        if (!empty($data['dob'])) {
            try {
                $data['dob'] = \Carbon\Carbon::parse($data['dob'])->format('Y-m-d');
            } catch (\Exception $e) {
                // If parsing fails, we can either keep it as is (and let DB fail if type is strict) 
                // or null it out if it's invalid.
                $data['dob'] = null; 
            }
        }

        if (!empty($data['tob'])) {
            try {
                $data['tob'] = \Carbon\Carbon::parse($data['tob'])->format('H:i:s');
            } catch (\Exception $e) {
                $data['tob'] = null;
            }
        }

        $consultation = AstrologerConsultation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Consultation request submitted successfully!',
            'data' => $consultation
        ], 201);
    }

}
