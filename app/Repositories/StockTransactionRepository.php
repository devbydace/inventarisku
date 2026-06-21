<?php

namespace App\Repositories;

use App\Models\StockTransaction;
use App\Repositories\Interfaces\StockTransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StockTransactionRepository implements StockTransactionRepositoryInterface
{
    public function findAll(): Collection
    {
        return StockTransaction::all();
    }

    public function findById(int $id): ?StockTransaction
    {
        return StockTransaction::find($id);
    }

    public function create(array $data): StockTransaction
    {
        return StockTransaction::create($data);
    }

    public function update(int $id, array $data): StockTransaction
    {
        $transaction = StockTransaction::findOrFail($id);
        $transaction->update($data);
        return $transaction;
    }

    public function findByStatus(string $status): Collection
    {
        return StockTransaction::where('status', $status)->get();
    }

    public function findByProduct(int $productId): Collection
    {
        return StockTransaction::where('product_id', $productId)->get();
    }
}