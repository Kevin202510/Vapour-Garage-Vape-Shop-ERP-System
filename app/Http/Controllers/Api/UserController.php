<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\SyncPermissionsRequest;
use App\Http\Requests\User\SyncRolesRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(protected UserService $userService) {}

    /**
     * GET /api/users
     *
     * Supports filters: branch_id, role, is_active, search, sort_by, sort_order, with_trashed
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        info("Data");
        $this->authorize('view users');

        $filters = $request->only([
            'branch_id', 'role', 'is_active', 'search',
            'sort_by', 'sort_order', 'with_trashed',
        ]);

        // Branch managers can only see users in their own branch
        if ($request->user()->isBranchManager() && ! $request->user()->isSuperAdmin()) {
            $filters['branch_id'] = $request->user()->branch_id;
        }

        $users = $this->userService->list($filters, $request->integer('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * POST /api/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * GET /api/users/{user}
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('view users');

        $user = $this->userService->findOrFail($id);

        return response()->json(['user' => new UserResource($user)]);
    }

    /**
     * PUT /api/users/{user}
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * DELETE /api/users/{user}  (soft delete)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('delete users');

        // Prevent self-deletion
        if ($request->user()->id === $id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $this->userService->delete($id);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * POST /api/users/{user}/restore
     */
    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete users');

        $this->userService->restore($id);

        return response()->json(['message' => 'User restored successfully.']);
    }

    /**
     * POST /api/users/{user}/activate
     */
    public function activate(int $id): JsonResponse
    {
        $this->authorize('manage users');

        $this->userService->activate($id);

        return response()->json(['message' => 'User activated.']);
    }

    /**
     * POST /api/users/{user}/deactivate
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage users');

        if ($request->user()->id === $id) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $this->userService->deactivate($id);

        return response()->json(['message' => 'User deactivated.']);
    }

    /**
     * PUT /api/users/{user}/roles  — replace all roles
     */
    public function syncRoles(SyncRolesRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->syncRoles($id, $request->roles, $request->user()->employee_id, $request->ip());

        return response()->json([
            'message' => 'Roles updated.',
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * PUT /api/users/{user}/permissions  — replace direct permissions
     */
    public function syncPermissions(SyncPermissionsRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->syncPermissions($id, $request->permissions, $request->user()->employee_id, $request->ip());

        return response()->json([
            'message' => 'Permissions updated.',
            'user'    => new UserResource($user),
        ]);
    }
}
