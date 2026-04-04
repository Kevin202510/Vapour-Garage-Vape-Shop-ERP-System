<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DiscountService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected DiscountService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view discounts');
        if ($request->boolean('valid_now')) {
            return response()->json(['data' => $this->service->getValidNow()]);
        }
        return response()->json($this->service->list($request->only(['is_active','type','search']), $request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create discounts');
        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'type'        => ['required','in:percentage,fixed_amount,buy_x_get_y'],
            'value'       => ['required','numeric','min:0'],
            'valid_from'  => ['nullable','date'],
            'valid_until' => ['nullable','date','after_or_equal:valid_from'],
            'is_active'   => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Discount created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view discounts');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit discounts');
        $validated = $request->validate([
            'name'        => ['sometimes','string','max:255'],
            'type'        => ['sometimes','in:percentage,fixed_amount,buy_x_get_y'],
            'value'       => ['sometimes','numeric','min:0'],
            'valid_from'  => ['sometimes','nullable','date'],
            'valid_until' => ['sometimes','nullable','date'],
            'is_active'   => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Discount updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete discounts');
        $this->service->delete($id);
        return response()->json(['message' => 'Discount deleted.']);
    }
}
