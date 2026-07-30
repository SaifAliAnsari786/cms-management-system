<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Menus",
 *     description="API endpoints for managing navigation menus"
 * )
 */
class MenuController extends Controller
{
    /**
     * Display a listing of menus.
     *
     * @OA\Get(
     *     path="/api/menus",
     *     tags={"Menus"},
     *     summary="List menus",
     *     description="Returns a paginated list of menus. Supports searching by title and filtering by status.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term matched against menu title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by menu status",
     *         required=false,
     *         @OA\Schema(type="string", example="active")
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
     *         description="Paginated list of menus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menus fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Menu")
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $menus = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Menus fetched successfully.',
            'data' => MenuResource::collection($menus->items()),
            'pagination' => [
                'current_page' => $menus->currentPage(),
                'last_page'    => $menus->lastPage(),
                'per_page'     => $menus->perPage(),
                'total'        => $menus->total(),
                'from'         => $menus->firstItem(),
                'to'           => $menus->lastItem(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/menus",
     *     tags={"Menus"},
     *     summary="Create a new menu",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Main Navigation"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Menu")
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = Menu::create($request->validated());

        return response()->json($menu, 201);
    }

    /**
     * Display the specified menu.
     *
     * @OA\Get(
     *     path="/api/menus/{menu}",
     *     tags={"Menus"},
     *     summary="Get a single menu",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="ID of the menu",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu fetched successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Menu")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function show(Menu $menu): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Menu fetched successfully.',
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Update the specified menu.
     *
     * @OA\Put(
     *     path="/api/menus/{menu}",
     *     tags={"Menus"},
     *     summary="Update a menu",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="ID of the menu",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Main Navigation"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Menu")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $menu->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully.',
            'data' => new MenuResource($menu->fresh()),
        ]);
    }

    /**
     * Remove the specified menu.
     *
     * @OA\Delete(
     *     path="/api/menus/{menu}",
     *     tags={"Menus"},
     *     summary="Delete a menu",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="menu",
     *         in="path",
     *         description="ID of the menu",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Menu not found")
     * )
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully.',
        ]);
    }
}