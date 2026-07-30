<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Throwable;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="API endpoints for managing permissions"
 * )
 */
class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     *
     * @OA\Get(
     *     path="/api/permissions",
     *     tags={"Permissions"},
     *     summary="List permissions",
     *     description="Returns a paginated list of permissions. Supports searching by name.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term matched against permission name",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Paginated list of permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Permission")
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
        $permissions = Permission::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return PermissionResource::collection($permissions);
    }

    /**
     * Store a newly created permission.
     *
     * @OA\Post(
     *     path="/api/permissions",
     *     tags={"Permissions"},
     *     summary="Create a new permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="posts.publish")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Permission")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(StorePermissionRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully.',
                'data' => new PermissionResource($permission),
            ], 201);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Permission creation failed.', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create permission.',
            ], 500);
        }
    }

    /**
     * Display the specified permission.
     *
     * @OA\Get(
     *     path="/api/permissions/{permission}",
     *     tags={"Permissions"},
     *     summary="Get a single permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="ID of the permission",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Permission")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Permission not found")
     * )
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'data' => new PermissionResource($permission),
        ]);
    }

    /**
     * Update the specified permission.
     *
     * @OA\Put(
     *     path="/api/permissions/{permission}",
     *     tags={"Permissions"},
     *     summary="Update a permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="ID of the permission",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="posts.publish")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Permission")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $permission->update([
                'name' => $data['name'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully.',
                'data' => new PermissionResource($permission->fresh()),
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Permission update failed.', [
                'permission_id' => $permission->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update permission.',
            ], 500);
        }
    }

    /**
     * Remove the specified permission.
     *
     * @OA\Delete(
     *     path="/api/permissions/{permission}",
     *     tags={"Permissions"},
     *     summary="Delete a permission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         description="ID of the permission",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Permission deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Permission $permission)
    {
        DB::beginTransaction();

        try {

            $permission->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully.',
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Permission deletion failed.', [
                'permission_id' => $permission->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete permission.',
            ], 500);
        }
    }
}