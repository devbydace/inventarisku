<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockTransaction extends Model
{
    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'supplier_id', 'reference_no', 'notes', 'status'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeApprovedBy($userId): bool
    {
        return $this->user_id !== $userId;
    }
}