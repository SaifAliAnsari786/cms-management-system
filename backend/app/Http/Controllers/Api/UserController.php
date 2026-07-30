<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API endpoints for managing users"
 * )
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="List users",
     *     description="Returns a paginated list of users. Supports searching by name or email.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term matched against user name or email",
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
     *         description="Paginated list of users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
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
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     *
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Creates a user and assigns a role.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Secret123!"),
     *             @OA\Property(property="role", type="string", example="editor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data'    => new UserResource($user),
            ], 201);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('User creation failed.', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create user.',
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Get a single user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     *
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     description="Updates a user's details and role. Password is only changed if provided.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", nullable=true, example="NewSecret123!"),
     *             @OA\Property(property="role", type="string", example="editor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $user->update([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => !empty($data['password'])
                    ? Hash::make($data['password'])
                    : $user->password,
            ]);

            $user->syncRoles([$data['role']]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data'    => new UserResource($user->fresh()),
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('User update failed.', [
                'user_id' => $user->id,
                'error'   => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update user.',
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     *
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     description="Deletes a user. A user cannot delete their own account.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete own account",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You cannot delete your own account.")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);

        } catch (Throwable $th) {

            DB::rollBack();

            Log::error('User deletion failed.', [
                'user_id' => $user->id,
                'error'   => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete user.',
            ], 500);
        }
    }
}