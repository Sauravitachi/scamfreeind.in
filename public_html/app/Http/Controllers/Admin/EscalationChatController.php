<?php

namespace App\Http\Controllers\Admin;

use App\Constants\FileDirectory;
use App\Http\Requests\Admin\EscalationChatRequest;
use App\Models\Escalation;
use App\Services\EscalationService;
use App\Services\ResponseService;
use App\Services\UploadedFileService;
use Illuminate\Support\Facades\Auth;

class EscalationChatController extends \App\Foundation\Controller
{
    /**
     * Constructor for EscalationChatController
     */
    public function __construct(
        protected EscalationService $service,
        protected UploadedFileService $uploadedFileService,
        protected ResponseService $responseService,
    ) {}

    public function store(EscalationChatRequest $request, Escalation $escalation)
    {
        $user = $request->user();

        abort_if(! $this->service->canUserChat($escalation, $user), 403, 'You are authorized to chat in this escalation.');
        abort_if(! $this->service->isChattable($escalation, $user), 403, 'Cannot chat in this escalation.');

        $file = $request->hasFile('file') ? $this->uploadedFileService->uploadFromRequest($request, 'file', FileDirectory::ESCALATIONS) : null;

        $this->service->createChat($escalation, $request, Auth::user(), $file);

        return $this->responseService->json(success: true);
    }
}
