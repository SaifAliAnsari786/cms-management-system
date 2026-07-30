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
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Pages",
 *     description="API endpoints for managing CMS pages, including soft delete/restore and scheduled publishing"
 * )
 */
class PageController extends Controller
{
    /**
     * Display a listing of pages.
     *
     * @OA\Get(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="List pages",
     *     description="Returns a paginated list of pages. Supports searching by title and filtering by status and menu.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term matched against page title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by page status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft","published"})
     *     ),
     *     @OA\Parameter(
     *         name="menu_id",
     *         in="query",
     *         description="Filter pages belonging to a specific menu",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of pages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Page")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
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
     *
     * @OA\Post(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="Create a new page",
     *     description="Creates a page. If published_at is in the future, status is set to draft; otherwise published.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title"},
     *                 @OA\Property(property="title", type="string", example="About Us"),
     *                 @OA\Property(property="content", type="string", example="<p>Page body</p>"),
     *                 @OA\Property(property="published_at", type="string", format="date-time", nullable=true, example="2026-08-15 09:00:00"),
     *                 @OA\Property(property="cover_image", type="string", format="binary", nullable=true),
     *                 @OA\Property(
     *                     property="menu_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={1,2}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Page created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Page")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
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
            $data['cover_image'] = upload_image(
                $request->file('cover_image'),
                'pages'
            );
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
     *
     * @OA\Get(
     *     path="/api/pages/{page}",
     *     tags={"Pages"},
     *     summary="Get a single page",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="ID of the page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Page")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Page not found")
     * )
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
     *
     * @OA\Put(
     *     path="/api/pages/{page}",
     *     tags={"Pages"},
     *     summary="Update a page",
     *     description="Updates a page. Replaces the cover image (deleting the old one) if a new file is sent, and recalculates status from published_at.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="ID of the page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title"},
     *                 @OA\Property(property="title", type="string", example="About Us"),
     *                 @OA\Property(property="content", type="string", example="<p>Page body</p>"),
     *                 @OA\Property(property="published_at", type="string", format="date-time", nullable=true, example="2026-08-15 09:00:00"),
     *                 @OA\Property(property="cover_image", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Page")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Page not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
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

            $data['cover_image'] = upload_image(
                $request->file('cover_image'),
                'pages'
            );
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
     *
     * @OA\Delete(
     *     path="/api/pages/{page}",
     *     tags={"Pages"},
     *     summary="Soft delete a page",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         description="ID of the page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Page not found")
     * )
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
     *
     * @OA\Get(
     *     path="/api/pages/trash",
     *     tags={"Pages"},
     *     summary="List soft-deleted pages",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of trashed pages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Page")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
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
     *
     * @OA\Post(
     *     path="/api/pages/{id}/restore",
     *     tags={"Pages"},
     *     summary="Restore a soft-deleted page",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the soft-deleted page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page restored successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Page")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Page not found in trash")
     * )
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