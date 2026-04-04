<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected BranchService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view branches');
        $data = $this->service->list($request->only(['is_active','city','search','with_trashed']), $request->integer('per_page', 15));
        return response()->json($data);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create branches');
        $validated = $request->validate([
            'name'           => ['required','string','max:255'],
            'address'        => ['required','string'],
            'city'           => ['required','string','max:100'],
            'contact_number' => ['nullable','string','max:20'],
            'is_active'      => ['sometimes','boolean'],
        ]);
        $branch = $this->service->create($validated);
        return response()->json(['message' => 'Branch created.', 'data' => $branch], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view branches');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit branches');
        $validated = $request->validate([
            'name'           => ['sometimes','string','max:255'],
            'address'        => ['sometimes','string'],
            'city'           => ['sometimes','string','max:100'],
            'contact_number' => ['sometimes','nullable','string','max:20'],
            'is_active'      => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Branch updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete branches');
        $this->service->delete($id);
        return response()->json(['message' => 'Branch deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete branches');
        $this->service->restore($id);
        return response()->json(['message' => 'Branch restored.']);
    }
}
