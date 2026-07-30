<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of menus.
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

  public function store(StoreMenuRequest $request): JsonResponse
{
    $menu = Menu::create($request->validated());

    return response()->json($menu, 201);
}

    /**
     * Display the specified menu.
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