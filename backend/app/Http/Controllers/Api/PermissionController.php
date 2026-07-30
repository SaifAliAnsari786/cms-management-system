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

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
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
