<?php

namespace App\Services;

use App\Models\StockTransaction;
use App\Models\Approval;
use App\Models\AuditTrail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    /**
     * Approve a stock transaction
     *
     * @param StockTransaction $transaction
     * @param User $approver
     * @return Approval
     * @throws \Exception
     */
    public function approve(StockTransaction $transaction, $approver)
    {
        // Validate self-approval
        $this->validateSelfApproval($transaction, $approver);

        return DB::transaction(function () use ($transaction, $approver) {
            // Lock the transaction row to prevent race condition
            $lockedTransaction = StockTransaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedTransaction) {
                throw new \Exception('Transaksi tidak ditemukan');
            }

            if ($lockedTransaction->status !== 'pending') {
                throw new \Exception('Transaksi sudah di-approve oleh user lain');
            }

            // Get the product with row lock
            $product = Product::where('id', $lockedTransaction->product_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            // Update stock based on transaction type
            if ($lockedTransaction->type === 'in') {
                // Stock In: increase stock
                $product->current_stock += $lockedTransaction->quantity;
                $product->save();
            } elseif ($lockedTransaction->type === 'out') {
                // Stock Out: decrease stock
                if ($product->current_stock < $lockedTransaction->quantity) {
                    throw new \Exception('Stok tidak mencukupi. Stok saat ini: ' . $product->current_stock . ', yang diminta: ' . $lockedTransaction->quantity);
                }
                $product->current_stock -= $lockedTransaction->quantity;
                $product->save();
            }

            // Update transaction status
            $oldValues = ['status' => 'pending'];
            $newValues = ['status' => 'approved'];

            $lockedTransaction->status = 'approved';
            $lockedTransaction->save();

            // Create approval record
            $approval = Approval::create([
                'stock_transaction_id' => $lockedTransaction->id,
                'user_id' => $approver->id,
                'action' => 'approve',
                'notes' => null,
            ]);

            // Create audit trail
            AuditTrail::log(
                $approver,
                'StockTransaction',
                $lockedTransaction->id,
                'update',
                $oldValues,
                $newValues
            );

            return $approval;
        });
    }

    /**
     * Reject a stock transaction
     *
     * @param StockTransaction $transaction
     * @param User $rejector
     * @param string $rejectReason
     * @return Approval
     * @throws \Exception
     */
    public function reject(StockTransaction $transaction, $rejector, string $rejectReason)
    {
        // Validate reject reason
        if (strlen($rejectReason) < 10) {
            throw new \Exception('Alasan reject harus minimal 10 karakter');
        }

        return DB::transaction(function () use ($transaction, $rejector, $rejectReason) {
            // Lock the transaction row to prevent race condition
            $lockedTransaction = StockTransaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedTransaction) {
                throw new \Exception('Transaksi tidak ditemukan');
            }

            if ($lockedTransaction->status !== 'pending') {
                throw new \Exception('Transaksi sudah di-reject oleh user lain');
            }

            // Update transaction status
            $oldValues = ['status' => 'pending'];
            $newValues = ['status' => 'rejected'];

            $lockedTransaction->status = 'rejected';
            $lockedTransaction->notes = $lockedTransaction->notes . ' | REJECTED: ' . $rejectReason;
            $lockedTransaction->save();

            // Create approval record
            $approval = Approval::create([
                'stock_transaction_id' => $lockedTransaction->id,
                'user_id' => $rejector->id,
                'action' => 'reject',
                'notes' => $rejectReason,
            ]);

            // Create audit trail
            AuditTrail::log(
                $rejector,
                'StockTransaction',
                $lockedTransaction->id,
                'update',
                $oldValues,
                $newValues
            );

            return $approval;
        });
    }

    /**
     * Check if a user can approve a transaction
     *
     * @param StockTransaction $transaction
     * @param User $user
     * @return bool
     */
    public function canApprove(StockTransaction $transaction, $user): bool
    {
        // Must be pending
        if ($transaction->status !== 'pending') {
            return false;
        }

        // Cannot approve own transaction
        if ($transaction->user_id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Validate that user is not approving their own transaction
     *
     * @param StockTransaction $transaction
     * @param User $user
     * @return void
     * @throws \Exception
     */
    private function validateSelfApproval(StockTransaction $transaction, $user): void
    {
        if ($transaction->user_id === $user->id) {
            throw new \Exception('Tidak dapat approve transaksi sendiri');
        }
    }
}