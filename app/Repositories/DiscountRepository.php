<?php

namespace App\Repositories;

use App\Contracts\Repositories\DiscountRepositoryInterface;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Collection;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
    public function __construct(Discount $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Discount
    {
        return $this->model->with($relations)->find($id);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('name')->get();
    }

    public function getValidNow(): Collection
    {
        $today = now()->toDateString();

        return $this->model
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today))
            ->orderBy('name')
            ->get();
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['type']),      fn ($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['valid_now']), function ($q) {
                $today = now()->toDateString();
                $q->where('is_active', true)
                  ->where(fn ($s) => $s->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
                  ->where(fn ($s) => $s->whereNull('valid_until')->orWhere('valid_until', '>=', $today));
            })
            ->when(isset($filters['search']), fn ($q) => $q->where('name', 'like', '%' . $filters['search'] . '%'))
            ->orderBy('name');
    }
}
