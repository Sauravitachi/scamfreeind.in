<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScamStatusRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ScamStatusReportController extends Controller
{
    /**
     * Get daily scam status report
     * 
     * GET /api/scam-status-report
     */
    public function index(Request $request)
    {
        try {
            $dateRange = $request->get('date', now()->toDateString());
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();

            if (str_contains($dateRange, ' to ')) {
                $dates = explode(' to ', $dateRange);
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
            } else {
                $startDate = Carbon::parse($dateRange)->startOfDay();
                $endDate = Carbon::parse($dateRange)->endOfDay();
            }
            
            $statusFilter = $request->get('status');
            $salesStatus = $request->get('sales_status');
            $draftingStatus = $request->get('drafting_status');
            
            $statusTypeFilter = $request->get('status_type');
            if ($salesStatus && !$draftingStatus) $statusTypeFilter = 'sales';
            if ($draftingStatus && !$salesStatus) $statusTypeFilter = 'drafting';

            $causerIdFilter = $request->get('causer_id');
            $scamIdFilter = $request->get('scam_id');

            // 1. Restriction check: Sales and Drafting Executives only see their own transitions
            $user = auth()->user();
            if ($user && !$user->hasAnyRole(['Admin', 'Super Admin', 'Manager', 'Sub Admin', 'Product Head', 'MIS', 'Auditor', 'Tech Team'])) {
                $causerIdFilter = $user->id;
            }

            // 2. Fetch scam status records for the given date range
            $query = ScamStatusRecord::with(['scam.customer', 'status', 'causer'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('scam_id')
                ->orderBy('created_at')
                ->orderBy('id');

            if ($statusTypeFilter) {
                $query->where('status_type', $statusTypeFilter);
            }
            
            if ($salesStatus) {
                if ($salesStatus === 'Without Status') {
                    $query->whereNull('status_id')->where('status_type', 'sales');
                } else {
                    $query->whereHas('status', function($q) use ($salesStatus) {
                        $q->where('title', $salesStatus)->where('type', 'sales');
                    });
                }
            }

            if ($draftingStatus) {
                if ($draftingStatus === 'Without Status') {
                    $query->whereNull('status_id')->where('status_type', 'drafting');
                } else {
                    $query->whereHas('status', function($q) use ($draftingStatus) {
                        $q->where('title', $draftingStatus)->where('type', 'drafting');
                    });
                }
            }

            if ($causerIdFilter) {
                $query->where('causer_id', $causerIdFilter);
            }

            if ($scamIdFilter) {
                $query->where('scam_id', $scamIdFilter);
            }

            $records = $query->get();

            // 3. Determine previous status for each scam before the start date
            $scamIds = $records->pluck('scam_id')->unique();
            $previousStatuses = [];
            $lastBeforeRange = collect();
            
            if ($scamIds->isNotEmpty()) {
                // Fetch the last record created BEFORE the start of the date range
                $subQuery = ScamStatusRecord::select('scam_id', 'status_type', DB::raw('MAX(id) as last_record_id'))
                    ->whereIn('scam_id', $scamIds);

                if ($statusTypeFilter) {
                    $subQuery->where('status_type', $statusTypeFilter);
                }

                $subQuery->where('created_at', '<', $startDate)
                    ->groupBy('scam_id', 'status_type');

                $lastBeforeRange = ScamStatusRecord::whereIn('id', $subQuery->pluck('last_record_id'))
                    ->with('status')
                    ->get()
                    ->keyBy(function($record) {
                        return $record->scam_id . '_' . $record->status_type->value;
                    });
            }

            // 4. Analyze transitions
            $groupedByScamAndType = $records->groupBy(function($item) {
                return $item->scam_id . '_' . $item->status_type->value;
            });
            $allChanges = [];
            $summary = [];
            $totalChanges = 0;

            foreach ($groupedByScamAndType as $key => $typeRecords) {
                $previousStatus = isset($lastBeforeRange[$key]) 
                    ? ($lastBeforeRange[$key]->status?->title ?: 'Without Status') 
                    : 'Without Status';

                foreach ($typeRecords as $record) {
                    $currentStatus = $record->status?->title ?: 'Without Status';

                    if ($previousStatus !== $currentStatus) {
                        
                        $transition = "{$previousStatus} -> {$currentStatus}";

                        $isFiltered = false;
                        if ($statusFilter) {
                            if ($currentStatus != $statusFilter && $record->status?->slug != $statusFilter) {
                                $isFiltered = true;
                            }
                        }

                        if (!$isFiltered) {
                            $allChanges[] = [
                                'scam_id' => $record->scam_id,
                                'customer_number' => $record->scam?->customer?->phone_number ?? 'N/A',
                                'status_type' => ucfirst($record->status_type->value),
                                'user' => $record->causer?->name ?? 'System',
                                'from' => $previousStatus,
                                'to' => $currentStatus,
                                'time' => $record->created_at->format('H:i'),
                            ];

                            $summary[$transition] = ($summary[$transition] ?? 0) + 1;
                            $totalChanges++;
                        }
                    }

                    $previousStatus = $currentStatus;
                }
            }

            return response()->json([
                'date' => $dateRange,
                'total_changes' => $totalChanges,
                'changes' => $allChanges,
                'summary' => (object)$summary,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }
}
