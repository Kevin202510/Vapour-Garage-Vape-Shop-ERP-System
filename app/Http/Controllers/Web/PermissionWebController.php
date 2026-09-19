<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PermissionWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected PermissionService $permissionService) {}

    public function index()
    {
        $this->authorize('manage permissions');

        $groups = $this->permissionService->groupedWithRoles();

        return view('pages.permissions.index', compact('groups'));
    }
}
