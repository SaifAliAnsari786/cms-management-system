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

class UserController extends Controller
{
    /**
     * Display a listing of users.
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