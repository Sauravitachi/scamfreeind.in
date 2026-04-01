<?php

namespace App\Http\Controllers\Admin;

use App\Constants\FileDirectory;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Http\Requests\Admin\EscalationRequest;
use App\Models\Escalation;
use App\Models\EscalationChat;
use App\Services\ActivityLogService;
use App\Services\EscalationService;
use App\Services\HelperService;
use App\Services\ResponseService;
use App\Services\UploadedFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EscalationController extends \App\Foundation\Controller
{
    /**
     * Constructor for EscalationController
     */
    public function __construct(
        protected UploadedFileService $uploadedFileService,
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected HelperService $helperService,
        protected EscalationService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit([Permission::ESCALATION_LIST, Permission::ESCALATION_LIST_SELF], only: ['index', 'show']),
            permit(Permission::ESCALATION_CREATE, only: ['create']),
            permit(Permission::ESCALATION_DELETE, only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->dataTable($request)->toJson();
        }

        $this->activityLogService->visited('escalations list');

        return view('admin.escalations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->activityLogService->visited('create escalation');

        return view('admin.escalations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EscalationRequest $request): JsonResponse
    {

        $file = $request->hasFile('file') ? $this->uploadedFileService->uploadFromRequest($request, 'file', FileDirectory::ESCALATIONS) : null;

        $escalation = $this->service->create(request: $request, escalatedByUser: $request->user(), file: $file);

        $this->activityLogService->created('escalation', $escalation);

        if ($request->toast) {
            return $this->responseService->json(success: true, data: $escalation, toast: ['type' => 'success', 'message' => 'Escalation Created!']);
        }

        $this->flashToast('success', 'Escalation Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.escalations.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Escalation $escalation)
    {
        abort_if($request->user()->cannot('view', $escalation), 403, 'Unauthorized Access!');

        $this->helperService->requestNotificationMarkAsRead($request);

        $this->activityLogService->visited('escalation detail', $escalation);

        $user = $request->user();

        if ($request->ajax()) {

            $escalation->load([
                'scam:id,sales_assignee_id,drafting_assignee_id,service_assignee_id',
                'chats' => fn ($q) => $q->oldest('created_at')->orderBy('id', 'ASC'),
                'chats.user:id,name,username,avatar,profile_picture_id',
                'chats.file:id,path,mime,original_name',
            ]);

            // canReject
            $escalation->can_reject = $this->service->isRejectable($escalation) && $this->service->canUserReject($escalation, $user);
            $escalation->can_close = $this->service->isClosable($escalation) && $this->service->canUserClose($escalation, $user);

            $escalation->append(['is_closed']);

            $escalation->chats->each(function (EscalationChat &$chat) {
                $chat->user->append(['username_with_role_name', 'profile_avatar']);
                $chat->file?->append(['url', 'is_previewable_file']);
                $chat->created_at_chat_formatted = $chat->created_at?->diffForHumans();
            });

            return $this->responseService->json(success: true, data: $escalation);

        }

        return view('admin.escalations.show', compact('escalation'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Escalation $escalation)
    {
        $this->service->delete($escalation);
        $this->activityLogService->deleted('escalation', $escalation);

        return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Escalation Deleted!']);
    }

    /**
     * Reject the escalation
     */
    public function reject(Request $request, Escalation $escalation)
    {
        abort_if(! $this->service->canUserReject($escalation, $request->user()), 403, 'You are not authorized to reject this escalation.');
        abort_if(! $this->service->isRejectable($escalation), 403, 'Cannot reject this escalation.');
        $this->service->reject($escalation);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Escalation Rejected!'));
    }

    /**
     * Close the escalation
     */
    public function close(Request $request, Escalation $escalation)
    {
        abort_if(! $this->service->canUserClose($escalation, $request->user()), 403, 'You are not authorized to close this escalation.');
        abort_if(! $this->service->isClosable($escalation), 403, 'Cannot reject this escalation.');
        $this->service->close($escalation);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Escalation Closed!'));
    }
}
