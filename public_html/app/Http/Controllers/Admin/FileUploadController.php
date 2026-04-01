<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\FileUploadRequest;
use App\Services\ResponseService;
use App\Services\UploadedFileService;
use Illuminate\Http\JsonResponse;

class FileUploadController extends \App\Foundation\Controller
{
    public function __construct(
        protected UploadedFileService $uploadedFileService,
        protected ResponseService $responseService
    ) {}

    public function store(FileUploadRequest $request): JsonResponse
    {
        $uploadedFile = $this->uploadedFileService->uploadFromRequest(
            request: $request,
            fieldName: 'file',
            directory: $request->file_directory
        );

        return $this->responseService->json(success: true, data: $uploadedFile);
    }
}
