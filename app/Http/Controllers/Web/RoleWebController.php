<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request)
    {
        $this->authorize('manage roles');

        $roles = $this->roleService->list($request->only('search'), 20);

        return view('pages.roles.index', [
            'roles'     => $roles,
            'protected' => RoleService::PROTECTED_ROLES,
        ]);
    }

    public function create()
    {
        $this->authorize('manage roles');

        return view('pages.roles.form', [
            'permissionGroups' => $this->roleService->permissionsGroupedByModule(),
            'assignedNames'    => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage roles');

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'permissions'     => ['array'],
            'permissions.*'   => ['string', 'exists:permissions,name'],
        ]);

        try {
            $role = $this->roleService->create($validated, auth()->user()->employee_id, $request->ip());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(int $id)
    {
        $this->authorize('manage roles');
        $role = $this->roleService->findOrFail($id);

        return view('pages.roles.form', [
            'role'              => $role,
            'permissionGroups'  => $this->roleService->permissionsGroupedByModule(),
            'assignedNames'     => $role->permissions->pluck('name'),
            'isProtected'       => $this->roleService->isProtected($role->name),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage roles');

        $validated = $request->validate([
            'name'            => ['sometimes', 'string', 'max:255'],
            'permissions'     => ['array'],
            'permissions.*'   => ['string', 'exists:permissions,name'],
        ]);

        try {
            $role = $this->roleService->update($id, $validated, auth()->user()->employee_id, $request->ip());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorize('manage roles');

        try {
            $this->roleService->delete($id, auth()->user()->employee_id, $request->ip());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }
}
