<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DepartmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DepartmentController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected DepartmentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view employees');
        return response()->json(['data' => $this->service->allWithCount()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create employees');
        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);
        return response()->json(['message' => 'Department created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view employees');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit employees');
        $validated = $request->validate([
            'name'        => ['sometimes','string','max:255'],
            'description' => ['sometimes','nullable','string'],
        ]);
        return response()->json(['message' => 'Department updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete employees');
        $this->service->delete($id);
        return response()->json(['message' => 'Department deleted.']);
    }
}
