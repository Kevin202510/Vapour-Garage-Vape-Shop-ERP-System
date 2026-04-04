<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    public function __construct(protected CustomerRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Customer
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Customer [{$id}] not found.");
    }

    public function create(array $data): Customer
    {
        if (isset($data['email'])) {
            $this->guardDuplicateEmail($data['email']);
        }
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->findOrFail($id);

        if (isset($data['email']) && $data['email'] !== $customer->email) {
            $this->guardDuplicateEmail($data['email'], $id);
        }

        return $this->repo->update($customer, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function addPoints(int $id, int $points): Customer
    {
        return $this->repo->addLoyaltyPoints($this->findOrFail($id), $points);
    }

    public function deductPoints(int $id, int $points): Customer
    {
        return $this->repo->deductLoyaltyPoints($this->findOrFail($id), $points);
    }

    private function guardDuplicateEmail(string $email, ?int $excludeId = null): void
    {
        $exists = Customer::where('email', $email)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered to another customer.'],
            ]);
        }
    }
}
