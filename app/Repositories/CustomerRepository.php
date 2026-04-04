<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Customer
    {
        return $this->model
            ->with($relations ?: ['registeredBranch', 'transactions'])
            ->withTrashed()
            ->find($id);
    }

    public function findByEmail(string $email): ?Customer
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?Customer
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function addLoyaltyPoints(Customer $customer, int $points): Customer
    {
        $customer->increment('loyalty_points', $points);
        return $this->updateTier($customer->fresh());
    }

    public function deductLoyaltyPoints(Customer $customer, int $points): Customer
    {
        $current = $customer->loyalty_points;
        $customer->update(['loyalty_points' => max(0, $current - $points)]);
        return $this->updateTier($customer->fresh());
    }

    public function updateTier(Customer $customer): Customer
    {
        $tier = match (true) {
            $customer->loyalty_points >= 10000 => 'platinum',
            $customer->loyalty_points >= 5000  => 'gold',
            $customer->loyalty_points >= 1000  => 'silver',
            default                            => 'bronze',
        };

        $customer->update(['tier' => $tier]);

        return $customer->fresh();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['registeredBranch'])
            ->when(isset($filters['branch_id']),    fn ($q) => $q->where('registered_branch_id', $filters['branch_id']))
            ->when(isset($filters['tier']),         fn ($q) => $q->where('tier', $filters['tier']))
            ->when(isset($filters['with_trashed']), fn ($q) => $q->withTrashed())
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name',  'like', '%' . $filters['search'] . '%')
                  ->orWhere('email',      'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone',      'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy('last_name');
    }
}
