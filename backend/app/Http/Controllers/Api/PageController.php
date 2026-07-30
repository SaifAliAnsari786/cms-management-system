<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request)
    {
        $pages = Page::query()
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return PageResource::collection($pages);
    }

    /**
     * Store a newly created page.
     */
    public function store(StorePageRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('pages', 'public');
        }

        $data['created_by'] = Auth::id();

        $page = Page::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data' => new PageResource($page),
        ], 201);
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Update the specified page.
     */
    public function update(UpdatePageRequest $request, Page $page)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {

            if ($page->cover_image && Storage::disk('public')->exists($page->cover_image)) {
                Storage::disk('public')->delete($page->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')
                ->store('pages', 'public');
        }

        $data['updated_by'] = Auth::id();

        $page->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data' => new PageResource($page->fresh()),
        ]);
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.',
        ]);
    }
}