<?php

namespace App\Actions\Scams;

use App\Enums\ScamActivityEvent;
use App\Http\Requests\Admin\BulkRecycleScamRequest;
use App\Models\Scam;
use App\Models\ScamActivity;
use App\Models\Scopes\NonRecycledScope;
use Illuminate\Support\Facades\DB;

class BulkRecycleScam
{
    public function handle(BulkRecycleScamRequest $request)
    {
        DB::transaction(function () use ($request) {

            $now = now();

            $scamIds = $request->validated('scams', []);

            // markind old ones first
            Scam::whereIn('id', $scamIds)->update(['recycled_at' => $now]);
            ScamActivity::insert(array_map(function (int $scamId) use ($request, $now) {
                return [
                    'scam_id' => $scamId,
                    'description' => 'recycled scam',
                    'user_id' => $request->user()?->id,
                    'event' => ScamActivityEvent::RECYCLED,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $scamIds));

            $scams = Scam::withoutGlobalScope(NonRecycledScope::class)->whereIn('id', $scamIds)->get();

            foreach ($scams as $scam) {
                $newData = $scam->only(['customer_id', 'scam_type_id', 'scam_amount', 'customer_description', 'scam_source_id', 'is_duplicate']);
                $newScam = Scam::make($newData);
                $newScam->recycled_parent_scam_id = $scam->id;
                $newScam->save();
            }
        });
    }
}
