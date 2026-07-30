<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Page::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pages = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Pages fetched successfully.',
            'data' => PageResource::collection($pages->items()),
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page'    => $pages->lastPage(),
                'per_page'     => $pages->perPage(),
                'total'        => $pages->total(),
                'from'         => $pages->firstItem(),
                'to'           => $pages->lastItem(),
            ],
        ], 200);
    }

    /**
     * Store a newly created page.
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        $slug = Str::slug($request->title);

        while (Page::where('slug', $slug)->exists()) {
            $slug = Str::slug($request->title) . '-' . Str::lower(Str::random(5));
        }

        $page = Page::create([
            'title'      => $request->title,
            'slug'       => $slug,
            'content'    => $request->content,
            'status'     => $request->status,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data'    => new PageResource($page),
        ], 201);
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Page fetched successfully.',
            'data'    => new PageResource($page),
        ], 200);
    }

    /**
     * Update the specified page.
     */
    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $slug = Str::slug($request->title);

        while (
            Page::where('slug', $slug)
                ->where('id', '!=', $page->id)
                ->exists()
        ) {
            $slug = Str::slug($request->title) . '-' . Str::lower(Str::random(5));
        }

        $page->update([
            'title'   => $request->title,
            'slug'    => $slug,
            'content' => $request->content,
            'status'  => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data'    => new PageResource($page->fresh()),
        ], 200);
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.',
        ], 200);
    }
}