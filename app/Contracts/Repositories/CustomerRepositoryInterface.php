<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Customer;

    public function findByEmail(string $email): ?Customer;

    public function findByPhone(string $phone): ?Customer;

    public function addLoyaltyPoints(Customer $customer, int $points): Customer;

    public function deductLoyaltyPoints(Customer $customer, int $points): Customer;

    public function updateTier(Customer $customer): Customer;

    public function restore(int $id): bool;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
