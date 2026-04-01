<?php

namespace App\Services;

use App\Http\Requests\Admin\UserPreferenceRequest;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class PreferenceService extends Service
{
    public function update(User $user, UserPreferenceRequest $request): void
    {
        DB::transaction(function () use ($user, $request): void {

            $data = $request->validated();
            $now = now();

            $preferences = collect($data)->map(fn ($value, $key): array => [
                'user_id' => $user->id,
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->toArray();

            UserPreference::upsert(values: $preferences, uniqueBy: ['user_id', 'key'], update: ['value', 'updated_at']);

        });
    }
}
