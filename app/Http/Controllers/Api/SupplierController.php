<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected SupplierService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view suppliers');
        return response()->json($this->service->list(
            $request->only(['is_active','search','with_trashed']),
            $request->integer('per_page', 15)
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create suppliers');
        $validated = $request->validate([
            'company_name'   => ['required','string','max:255'],
            'contact_person' => ['nullable','string','max:255'],
            'email'          => ['nullable','email','max:255'],
            'phone'          => ['nullable','string','max:20'],
            'address'        => ['nullable','string'],
            'tin'            => ['nullable','string','max:20'],
            'is_active'      => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Supplier created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view suppliers');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit suppliers');
        $validated = $request->validate([
            'company_name'   => ['sometimes','string','max:255'],
            'contact_person' => ['sometimes','nullable','string','max:255'],
            'email'          => ['sometimes','nullable','email','max:255'],
            'phone'          => ['sometimes','nullable','string','max:20'],
            'address'        => ['sometimes','nullable','string'],
            'tin'            => ['sometimes','nullable','string','max:20'],
            'is_active'      => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Supplier updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete suppliers');
        $this->service->delete($id);
        return response()->json(['message' => 'Supplier deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete suppliers');
        $this->service->restore($id);
        return response()->json(['message' => 'Supplier restored.']);
    }
}
