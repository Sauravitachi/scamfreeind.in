<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a paginated listing of published blogs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Blog::with('author:id,name,username')
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC');
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('summary', 'LIKE', "%{$search}%");
            });
        }

        $blogs = $query->paginate($request->query('limit', 10));

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    /**
     * Display the specified blog by slug.
     */
    public function show($slug): JsonResponse
    {
        $blog = Blog::with('author:id,name,username')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog post not found or it is not published yet.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }

    /**
     * Get latest blog posts.
     */
    public function latest(Request $request): JsonResponse
    {
        $blogs = Blog::with('author:id,name,username')
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit($request->query('limit', 3))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }
}
