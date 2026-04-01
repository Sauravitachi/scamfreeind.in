<?php

namespace App\Services;

use App\Constants\Permission;
use App\Enums\EscalationStatus;
use App\Enums\EscalationType;
use App\Http\Requests\Admin\EscalationChatRequest;
use App\Http\Requests\Admin\EscalationRequest;
use App\Models\Escalation;
use App\Models\EscalationChat;
use App\Models\UploadedFile;
use App\Models\User;
use App\Notifications\CaseEscalatedNotification;
use Illuminate\Contracts\Auth\Authenticatable as AuthUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\EloquentDataTable;

class EscalationService extends Service
{
    public function dataTable(Request $request): EloquentDataTable
    {
        $query = Escalation::query();

        $user = Auth::user();

        $query->with([
            'escalatedByUser:id,name,username,avatar,profile_picture_id',
            'scam:id,track_id,customer_id,scam_type_id,scam_amount,sales_assignee_id,drafting_assignee_id,service_assignee_id',
            'scam.customer:id,first_name,last_name,dial_code,phone_number',
            'scam.salesAssignee:id,name,username,avatar,profile_picture_id',
            'scam.draftingAssignee:id,name,username,avatar,profile_picture_id',
            'scam.serviceAssignee:id,name,username,avatar,profile_picture_id',
        ]);

        $query->leftJoin('users', 'escalations.escalated_by_user_id', '=', 'users.id');

        $query->select([
            'escalations.*',
        ]);

        if (! $user->can(Permission::ESCALATION_LIST->value) && $user->can(Permission::ESCALATION_LIST_SELF->value)) {
            $query->whereUserAssociated($user);
        }

        // Filters
        if ($statusFilter = $request->get('status')) {
            $query->where('escalations.status', $statusFilter);
        }

        $table = datatables()->eloquent($query);

        $table->editColumn('escalated_by_user', function (Escalation $e) {
            $e->escalatedByUser->append(['profileAvatar']);

            return $e->escalatedByUser;
        });
        $table->editColumn('status', fn (Escalation $e) => $e->status?->label());
        $table->editColumn('type', fn (Escalation $e) => $e->type?->label());
        $table->editColumn('created_at', fn (Escalation $e) => format_date($e->created_at));
        $table->editColumn('closed_at', fn (Escalation $e) => format_date($e->closed_at));

        $table->addColumn('customer_details', fn (Escalation $e) => $e->scam->customer->fullNameWithFullPhoneNumber);
        $table->addColumn('scam_details', function (Escalation $e) {
            $text = "#{$e->scam->track_id} - {$e->scam->scamType?->title}";
            $formattedAmount = $e->scam->scam_amount ? format_amount($e->scam->scam_amount) : null;
            if ($formattedAmount) {
                $text .= " - $formattedAmount";
            }

            return $text;
        });
        $table->addColumn('status_color', fn (Escalation $e) => $e->status->color());

        $table->addColumn('assignee', function (Escalation $e) {
            $assignee = match ($e->type) {
                EscalationType::SALES => $e->scam->salesAssignee,
                EscalationType::DRAFTING => $e->scam->draftingAssignee,
                EscalationType::SERVICE => $e->scam->serviceAssignee,
                default => null
            };
            if ($assignee) {
                $assignee->append(['profileAvatar']);
            }

            return $assignee ?? null;
        });

        $table->orderColumn('escalated_by_user', fn (Builder $q, string $order) => $q->orderBy('users.name', $order));
        $table->filterColumn('escalated_by_user', fn (Builder $q, string $keyword) => $q->where('users.name', 'LIKE', "%$keyword%"));

        $table->filterColumn('customer_details', function (Builder $query, string $keyword) {
            $query->whereHas('scam.customer', function (Builder $query) use ($keyword) {
                $query->whereSearch($keyword);
            });
        });

        return $table;
    }

    public function create(EscalationRequest $request, AuthUser|User $escalatedByUser, ?UploadedFile $file = null): Escalation
    {

        $escalation = Escalation::create([
            ...$request->only('scam_id', 'type'),
            'escalated_by_user_id' => $escalatedByUser->id,
        ]);

        $escalation->chats()->create([
            'file_id' => $file?->id,
            'message' => $request->validated('message'),
            'user_id' => $escalatedByUser->id,
        ]);

        // notifying scam assigned users
        Notification::sendNow(User::whereIn('id', [
            $escalation->scam->sales_assignee_id,
            $escalation->scam->drafting_assignee_id,
            $escalation->scam->service_assignee_id,
        ])->get(['id']), new CaseEscalatedNotification($escalation));

        return $escalation;
    }

    public function delete(Escalation $escalation): bool
    {
        return $escalation->delete(); // Also deletes the escaltion chats relation to this
    }

    public function canUserReject(Escalation $escalation, User|AuthUser $user): bool
    {
        return (
            $escalation->scam->sales_assignee_id == $user->id && $escalation->type == EscalationType::SALES && $user->canAny([Permission::SALES_MANAGEMENT, Permission::SALES_MANAGEMENT_SELF])) ||

            ($escalation->scam->drafting_assignee_id == $user->id && $escalation->type == EscalationType::DRAFTING && $user->canAny([Permission::DRAFTING_MANAGEMENT, Permission::DRAFTING_MANAGEMENT_SELF])) ||

            ($escalation->scam->service_assignee_id == $user->id && $escalation->type == EscalationType::SERVICE && $user->canAny([Permission::SERVICE_MANAGEMENT, Permission::SERVICE_MANAGEMENT_SELF])
            );
    }

    public function isRejectable(Escalation $escalation): bool
    {
        return $escalation->status !== EscalationStatus::CLOSED;
    }

    public function reject(Escalation $escalation): bool
    {
        return $escalation->update(['is_rejected' => true]);
    }

    public function canUserClose(Escalation $escalation, User|AuthUser $user): bool
    {
        $superCond = $user->can(Permission::ESCALATION_LIST);

        if ($superCond) {
            return true;
        }

        $selfCond = $user->can(Permission::SERVICE_MANAGEMENT_SELF, Permission::ESCALATION_LIST_SELF);

        return $selfCond && $escalation->scam->service_assignee_id == $user->id;
    }

    public function isClosable(Escalation $escalation): bool
    {
        return $escalation->status !== EscalationStatus::CLOSED;
    }

    public function close(Escalation $escalation): bool
    {
        return $escalation->update(['status' => EscalationStatus::CLOSED]);
    }

    public function createChat(Escalation $escalation, EscalationChatRequest $request, User|AuthUser $user, ?UploadedFile $file = null): EscalationChat
    {
        return $escalation->chats()->create([
            'message' => $request->validated('message'),
            'file_id' => $file?->id,
            'user_id' => $user->id,
        ]);
    }

    public function getEscalationTitle(Escalation $escalation): string
    {
        $escalation->load([
            'scam:id,scam_type_id,customer_id,scam_amount',
            'scam.scamType:id,title',
            'scam.customer:id,first_name,last_name,dial_code,phone_number',
        ]);

        $title = $escalation->scam->customer->full_name.' | '.$escalation->scam->scamType->title;

        if (! is_null($scamAmount = $escalation->scam->scam_amount)) {
            $title .= ' | '.format_amount($scamAmount);
        }

        return $title;
    }

    public function canUserChat(Escalation $escalation, User|AuthUser $user): bool
    {
        if ($user->can(Permission::ESCALATION_LIST->value)) {
            return true;
        }

        return $user->can(Permission::ESCALATION_LIST_SELF) && $escalation->isUserAssociated($user);
    }

    public function isChattable(Escalation $escalation, User|AuthUser $user): bool
    {
        return $escalation->status !== EscalationStatus::CLOSED;
    }
}
