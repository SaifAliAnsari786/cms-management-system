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
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('menu_id'), function ($query) use ($request) {
                $query->whereHas('menus', function ($q) use ($request) {
                    $q->where('id', $request->menu_id);
                });
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

        if (!empty($data['published_at'])) {
            $data['status'] = now()->lt($data['published_at'])
                ? 'draft'
                : 'published';
        }

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

        if (!empty($data['published_at'])) {
            $data['status'] = now()->lt($data['published_at'])
                ? 'draft'
                : 'published';
        }
        
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

    /**
     * Display all soft deleted pages.
     */
    public function trash()
    {
        $pages = Page::onlyTrashed()
            ->latest()
            ->paginate(10);

        return PageResource::collection($pages);
    }

    /**
     * Restore a soft deleted page.
     */
    public function restore($id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);

        $page->restore();

        return response()->json([
            'success' => true,
            'message' => 'Page restored successfully.',
            'data' => new PageResource($page),
        ]);
    }   

}