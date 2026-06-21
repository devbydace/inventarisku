<?php

namespace App\Repositories\Interfaces;

use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Collection;

interface StockTransactionRepositoryInterface
{
    public function findAll(): Collection;
    public function findById(int $id): ?StockTransaction;
    public function create(array $data): StockTransaction;
    public function update(int $id, array $data): StockTransaction;
    public function findByStatus(string $status): Collection;
    public function findByProduct(int $productId): Collection;
}