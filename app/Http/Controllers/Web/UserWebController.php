<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;

    public function __construct(
        protected UserService $userService,
        protected BranchService $branchService,
        protected RoleRepositoryInterface $roleRepository,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('view users');
        $user    = auth()->user();
        $filters = $request->only(['search', 'branch_id', 'role', 'is_active']);

        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }

        $users    = $this->userService->list($filters, 20)->withQueryString();
        $branches = $this->branchService->allActive();
        $roles    = $this->roleRepository->all();

        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $totalUsers    = DB::table('users')->whereNull('deleted_at')
            ->when($filters['branch_id'] ?? null, fn ($q) => $q->where('branch_id', $filters['branch_id']))->count();
        $activeUsers   = DB::table('users')->whereNull('deleted_at')->where('is_active', true)
            ->when($filters['branch_id'] ?? null, fn ($q) => $q->where('branch_id', $filters['branch_id']))->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        return view('pages.users.index', compact(
            'users', 'branches', 'roles', 'effectiveBranchId', 'activeBranchLabel',
            'totalUsers', 'activeUsers', 'inactiveUsers'
        ));
    }

    public function create()
    {
        $this->authorize('create users');
        $branches = $this->branchService->allActive();
        $roles    = $this->roleRepository->all();

        return view('pages.users.form', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create users');

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::min(8)->mixedCase()->numbers()],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = $this->userService->create($validated);

        if ($request->filled('roles') && auth()->user()->can('manage roles')) {
            $this->userService->syncRoles($user->id, $request->input('roles', []), auth()->user()->employee_id, $request->ip());
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} created successfully.");
    }

    public function show(int $id)
    {
        $this->authorize('view users');
        $user = $this->userService->findOrFail($id);

        return view('pages.users.show', compact('user'));
    }

    public function edit(int $id)
    {
        $this->authorize('edit users');
        $user     = $this->userService->findOrFail($id);
        $branches = $this->branchService->allActive();
        $roles    = $this->roleRepository->all();

        return view('pages.users.form', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit users');

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password'  => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        $user = $this->userService->update($id, $validated);

        if ($request->has('roles') && auth()->user()->can('manage roles')) {
            $this->userService->syncRoles($user->id, $request->input('roles', []), auth()->user()->employee_id, $request->ip());
        }

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorize('delete users');

        if ($request->user()->id === $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->userService->delete($id);

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function activate(int $id)
    {
        $this->authorize('manage users');
        $this->userService->activate($id);

        return back()->with('success', 'User activated.');
    }

    public function deactivate(Request $request, int $id)
    {
        $this->authorize('manage users');

        if ($request->user()->id === $id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $this->userService->deactivate($id);

        return back()->with('success', 'User deactivated.');
    }
}
