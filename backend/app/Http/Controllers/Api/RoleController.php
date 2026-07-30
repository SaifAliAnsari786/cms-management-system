<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Throwable;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="API endpoints for managing roles and their assigned permissions"
 * )
 */
class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @OA\Get(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="List roles",
     *     description="Returns a paginated list of roles with their permissions. Supports searching by name.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term matched against role name",
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
     *         description="Paginated list of roles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Role")
     *             ),
     *             @OA\Property(property="links", type="object", example={"first":"/api/roles?page=1","last":"/api/roles?page=2"}),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created role.
     *
     * @OA\Post(
     *     path="/api/roles",
     *     tags={"Roles"},
     *     summary="Create a new role",
     *     description="Creates a role and optionally assigns permissions to it.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="editor"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"posts.create","posts.edit"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(@OA\Property(property="message", type="string", example="The given data was invalid."))),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unable to create role.")))
     * )
     */
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => new RoleResource($role->load('permissions')),
            ], 201);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Role creation failed.', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create role.',
            ], 500);
        }
    }

    /**
     * Display the specified role.
     *
     * @OA\Get(
     *     path="/api/roles/{role}",
     *     tags={"Roles"},
     *     summary="Get a single role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the role",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found")
     * )
     */
    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => new RoleResource($role->load('permissions')),
        ]);
    }

    /**
     * Update the specified role.
     *
     * @OA\Put(
     *     path="/api/roles/{role}",
     *     tags={"Roles"},
     *     summary="Update a role",
     *     description="Updates a role's name and replaces its permission set.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the role",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="editor"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"posts.create","posts.edit"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Role not found"))),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(@OA\Property(property="message", type="string", example="The given data was invalid."))),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unable to update role.")))
     * )
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $role->update([
                'name' => $data['name'],
            ]);

            $role->syncPermissions($data['permissions'] ?? []);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data' => new RoleResource($role->load('permissions')),
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Role update failed.', [
                'role_id' => $role->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update role.',
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     *
     * @OA\Delete(
     *     path="/api/roles/{role}",
     *     tags={"Roles"},
     *     summary="Delete a role",
     *     description="Deletes a role. The built-in Admin role cannot be deleted.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="ID of the role",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Role not found"))),
     *     @OA\Response(
     *         response=422,
     *         description="Admin role cannot be deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Admin role cannot be deleted.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin role cannot be deleted.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $role->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('Role deletion failed.', [
                'role_id' => $role->id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete role.',
            ], 500);
        }
    }
}