<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected CustomerService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view customers');
        return response()->json($this->service->list(
            $request->only(['branch_id','tier','search','with_trashed']),
            $request->integer('per_page', 15)
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create customers');
        $validated = $request->validate([
            'registered_branch_id' => ['nullable','integer','exists:branches,id'],
            'first_name'           => ['required','string','max:100'],
            'last_name'            => ['required','string','max:100'],
            'email'                => ['nullable','email','max:255'],
            'phone'                => ['nullable','string','max:20'],
            'birth_date'           => ['nullable','date','before:-18 years'],
        ]);
        return response()->json(['message' => 'Customer created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view customers');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit customers');
        $validated = $request->validate([
            'first_name'  => ['sometimes','string','max:100'],
            'last_name'   => ['sometimes','string','max:100'],
            'email'       => ['sometimes','nullable','email','max:255'],
            'phone'       => ['sometimes','nullable','string','max:20'],
            'birth_date'  => ['sometimes','nullable','date','before:-18 years'],
        ]);
        return response()->json(['message' => 'Customer updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete customers');
        $this->service->delete($id);
        return response()->json(['message' => 'Customer deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete customers');
        $this->service->restore($id);
        return response()->json(['message' => 'Customer restored.']);
    }

    public function addPoints(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit customers');
        $validated = $request->validate(['points' => ['required','integer','min:1']]);
        return response()->json(['message' => 'Points added.', 'data' => $this->service->addPoints($id, $validated['points'])]);
    }
}
