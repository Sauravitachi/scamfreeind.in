<?php

namespace App\Services;

use App\Constants\ActivityEvent;
use App\Models\Scam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ActivityLogService extends Service
{
    public function visited(string $page, ?Model $entity = null): void
    {
        $this->logEvent($page, ActivityEvent::VISITED, $entity);
    }

    public function created(string $resourceOrMessage, ?Model $entity = null)
    {
        $this->logEvent($resourceOrMessage, ActivityEvent::CREATED, $entity);
    }

    public function updated(string $resourceOrMessage, ?Model $entity = null, array $properties = [])
    {
        $this->logEvent($resourceOrMessage, ActivityEvent::UPDATED, $entity, $properties);
    }

    public function deleted(string $resourceOrMessage, ?Model $entity = null)
    {
        $this->logEvent($resourceOrMessage, ActivityEvent::DELETED, $entity);
    }

    public function uploaded(string $resourceOrMessage, ?Model $entity = null)
    {
        $this->logEvent($resourceOrMessage, ActivityEvent::UPLOADED, $entity);
    }

    public function scamAssign(Scam $scam, string $assigneeType, ?int $assigneeId)
    {
        $assignEvent = match ($assigneeType) {
            'sales' => ActivityEvent::SCAM_ASSIGN_SALES,
            'drafting' => ActivityEvent::SCAM_ASSIGN_DRAFTING,
            'service' => ActivityEvent::SCAM_ASSIGN_SERVICE,
            'sub_admin' => ActivityEvent::SCAM_ASSIGN_SUB_ADMIN,
            default => throw new InvalidArgumentException("Invalid assignee type: {$assigneeType}")
        };

        $description = $assigneeId === null ? "Removed assignee from case #{$scam->track_id}." : "assigned case #{$scam->track_id} to {$assigneeType} memeber.";

        $this->logEvent($description, $assignEvent, $scam, [
            'assignee_type' => $assigneeType,
            'assignee_id' => $assigneeId,
        ]);
    }

    public function log(string $description, ActivityEvent $event, ?Model $entity = null, array $properties = [])
    {
        $this->logEvent(description: $description, event: $event, entity: $entity, properties: $properties);
    }

    public function changedSetting(string $description)
    {
        activity()->event(ActivityEvent::SETTINGS_CHANGED->value)->log($description);
    }

    public function logUnauthorizedAccess(Request $request): void
    {
        $route = $request->path();

        activity()
            ->event(ActivityEvent::UNAUTHORIZED_ACCESS->value)
            ->withProperties([
                'route' => $route,
                'roles' => $request->user()?->roles()->pluck('name'),
            ])
            ->log("Unauthorized access attempt on {$route}");
    }

    private function logEvent(string $description, ActivityEvent $event, ?Model $entity = null, array $properties = [])
    {
        $activity = activity()->event($event->value);
        if ($entity) {
            $activity->performedOn($entity);
        }
        $activity->withProperties($properties)->log($description);
    }
}
