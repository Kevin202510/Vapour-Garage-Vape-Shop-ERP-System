<?php

namespace App\Services;

use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DiscountRepositoryInterface;
use App\Contracts\Repositories\SalesTransactionRepositoryInterface;
use App\Models\SalesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(
        protected SalesTransactionRepositoryInterface $transactionRepo,
        protected BranchInventoryRepositoryInterface  $inventoryRepo,
        protected CustomerRepositoryInterface         $customerRepo,
        protected DiscountRepositoryInterface         $discountRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionRepo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): SalesTransaction
    {
        return $this->transactionRepo->findById($id)
            ?? throw new ModelNotFoundException("Transaction [{$id}] not found.");
    }

    /**
     * Process a complete POS sale.
     *
     * $data = [
     *   'branch_id'      => int,
     *   'cashier_id'     => int,
     *   'customer_id'    => int|null,
     *   'payment_method' => string,
     *   'amount_tendered'=> float,
     *   'items'          => [
     *     ['product_id' => int, 'quantity' => int, 'unit_price' => float, 'discount_id' => int|null],
     *     ...
     *   ],
     * ]
     */
    public function processTransaction(array $data): SalesTransaction
    {
        return DB::transaction(function () use ($data) {
            $subtotal       = 0;
            $totalDiscount  = 0;
            $totalTax       = 0;
            $lineItems      = [];

            foreach ($data['items'] as $item) {
                $lineSubtotal   = round($item['unit_price'] * $item['quantity'], 2);
                $discountAmount = 0;

                if (! empty($item['discount_id'])) {
                    $discount       = $this->discountRepo->findById($item['discount_id']);
                    $discountAmount = $discount?->computeDiscount($lineSubtotal) ?? 0;
                }

                $taxable   = $lineSubtotal - $discountAmount;
                $taxAmount = round($taxable * 0.12, 2);

                $subtotal      += $lineSubtotal;
                $totalDiscount += $discountAmount;
                $totalTax      += $taxAmount;

                $lineItems[] = array_merge($item, [
                    'discount_amount' => $discountAmount,
                    'tax_amount'      => $taxAmount,
                    'subtotal'        => $lineSubtotal,
                ]);
            }

            // ✅ Compute totals FIRST
            $totalAmount    = round($subtotal, 2);
            $amountTendered = $data['amount_tendered'];
            $change         = round($amountTendered - $totalAmount, 2);

            // ✅ Validate BEFORE any side effects
            if ($change < 0) {
                throw ValidationException::withMessages([
                    'amount_tendered' => [
                        'Insufficient payment. Short by ₱' . number_format(abs($change), 2)
                    ],
                ]);
            }

            // ✅ ONLY deduct stock AFTER payment is valid
            foreach ($lineItems as $item) {
                $this->inventoryRepo->decrementStock (
                    $data['branch_id'],
                    $item['product_id'],
                    $item['quantity']
                );
            }

            $transaction = $this->transactionRepo->create([
                'transaction_number' => SalesTransaction::generateTransactionNumber($data['branch_id']),
                'branch_id'          => $data['branch_id'],
                'cashier_id'         => $data['cashier_id'],
                'customer_id'        => $data['customer_id'] ?? null,
                'subtotal'           => $subtotal,
                'discount_amount'    => $totalDiscount,
                'tax_amount'         => $totalTax,
                'total_amount'       => $totalAmount,
                'amount_tendered'    => $amountTendered,
                'change_amount'      => $change,
                'payment_method'     => $data['payment_method'],
                'status'             => 'completed',
                'transaction_date'   => now(),
            ]);

            foreach ($lineItems as $line) {
                $transaction->items()->create([
                    'product_id'      => $line['product_id'],
                    'discount_id'     => $line['discount_id'] ?? null,
                    'quantity'        => $line['quantity'],
                    'unit_price'      => $line['unit_price'],
                    'discount_amount' => $line['discount_amount'],
                    'tax_amount'      => $line['tax_amount'],
                    'subtotal'        => $line['subtotal'],
                ]);
            }

            // Award loyalty points (1 point per ₱10 spent) for registered customers
            if (! empty($data['customer_id'])) {
                $customer = $this->customerRepo->findById($data['customer_id']);
                if ($customer) {
                    $points = (int) floor($totalAmount / 10);
                    $this->customerRepo->addLoyaltyPoints($customer, $points);
                }
            }

            return $transaction->load(['branch', 'cashier', 'customer', 'items.product', 'items.discount']);
        });
    }

    public function void(int $id, string $reason): SalesTransaction
    {
        $transaction = $this->findOrFail($id);

        if ($transaction->status !== 'completed') {
            throw new RuntimeException("Only completed transactions can be voided.");
        }

        return DB::transaction(function () use ($transaction, $reason) {
            // Restore inventory for each voided item
            foreach ($transaction->items as $item) {
                $this->inventoryRepo->incrementStock(
                    $transaction->branch_id,
                    $item->product_id,
                    $item->quantity
                );
            }

            // Reverse loyalty points if customer was attached
            if ($transaction->customer_id) {
                $customer = $this->customerRepo->findById($transaction->customer_id);
                if ($customer) {
                    $points = (int) floor($transaction->total_amount / 10);
                    $this->customerRepo->deductLoyaltyPoints($customer, $points);
                }
            }

            return $this->transactionRepo->void($transaction, $reason);
        });
    }

    public function getDailySummary(string $date, int $branchId): array
    {
        return $this->transactionRepo->getDailySummary($date, $branchId);
    }
}
