<?php

namespace App\Actions\ScamStatus;

use App\Exceptions\CycleDetectedException;
use App\Models\ScamStatus;
use App\Models\StatusTransition;
use Illuminate\Http\Request;

class StoreTransition
{
    public function handle(Request $request): void
    {
        $data = $request->input('transition', []);
        $type = $request->type;

        $this->validateNoCycles($data);

        $allStatusIds = ScamStatus::pluck('id')->toArray();

        foreach ($allStatusIds as $currentStatusId) {
            StatusTransition::where('current_status_id', $currentStatusId)
                ->where('type', $type)
                ->delete();

            $nextStatusIds = $data[$currentStatusId] ?? [];

            $insertData = collect($nextStatusIds)->map(function ($nextStatusId) use ($currentStatusId, $type) {
                return [
                    'current_status_id' => $currentStatusId,
                    'next_status_id' => $nextStatusId,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (! empty($insertData)) {
                StatusTransition::insert($insertData);
            }
        }
    }

    private function validateNoCycles(array $transitions): void
    {
        $graph = [];

        foreach ($transitions as $currentStatus => $nextStatuses) {
            $graph[$currentStatus] = $nextStatuses;
        }

        $visited = [];
        $recStack = [];

        foreach (array_keys($graph) as $node) {
            $path = [];
            if ($this->isCyclic($node, $graph, $visited, $recStack, $path)) {
                // Throw your custom exception and pass the cycle path
                throw new CycleDetectedException($path);
            }
        }
    }

    private function isCyclic($node, &$graph, &$visited, &$recStack, &$path): bool
    {
        if (! isset($visited[$node])) {
            $visited[$node] = true;
            $recStack[$node] = true;
            $path[] = $node;

            foreach ($graph[$node] ?? [] as $neighbour) {
                if (
                    (! isset($visited[$neighbour]) && $this->isCyclic($neighbour, $graph, $visited, $recStack, $path))
                    || (! empty($recStack[$neighbour]))
                ) {
                    $path[] = $neighbour;

                    return true;
                }
            }
        }

        $recStack[$node] = false;
        array_pop($path);

        return false;
    }
}
