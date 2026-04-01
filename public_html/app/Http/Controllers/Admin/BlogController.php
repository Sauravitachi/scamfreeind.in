<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\BlogService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends \App\Foundation\Controller
{
    /**
     * Constructor for BlogController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected BlogService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::BLOG_LIST, ['index']),
            permit(Permission::BLOG_CREATE, ['create', 'store']),
            permit(Permission::BLOG_UPDATE, ['edit', 'update']),
            permit(Permission::BLOG_DELETE, ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('blogs list');

        return view('admin.blog.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create blog');

        return view('admin.blog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request): JsonResponse
    {
        $blog = $this->service->create($request);

        $this->activityLogService->created('blog', $blog);

        $this->flashToast('success', 'Blog Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.blog.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): View
    {
        $this->activityLogService->visited('blog detail', $blog);

        return view('admin.blog.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog): View
    {
        $this->activityLogService->visited('edit blog', $blog);

        return view('admin.blog.edit', compact('blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, Blog $blog): JsonResponse
    {
        try {

            if ($this->service->update($blog, $request)) {
                $toast = ['type' => 'success', 'message' => 'Blog Updated!'];
                $this->activityLogService->updated('blog', $blog);
            } else {
                $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
            }

            return $this->responseService->json(success: true, toast: $toast);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog): JsonResponse
    {
        try {
            $this->service->delete($blog);

            $this->activityLogService->deleted('blog', $blog);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Blog Deleted!']);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
