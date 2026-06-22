<?php

namespace App\Livewire\Approval;

use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\AuditTrail;

class Index extends Component
{
    public $showRejectForm = false;
    public $rejectTransactionId = null;
    public $rejectReason = '';
    public $confirmApproveId = null;

    protected $rules = [
        'rejectReason' => 'required|string|min:10',
    ];

    protected $messages = [
        'rejectReason.required' => 'Alasan reject harus diisi',
        'rejectReason.min' => 'Alasan reject harus minimal 10 karakter',
    ];

    public function render()
    {
        $transactions = StockTransaction::with(['product', 'user'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.approval.index', compact('transactions'));
    }

    public function confirmApprove($transactionId)
    {
        $this->confirmApproveId = $transactionId;
    }

    public function approve($transactionId)
    {
        $transaction = StockTransaction::findOrFail($transactionId);

        // Business logic will be implemented in Phase 7b
        // For now, just update status
        $transaction->update(['status' => 'approved']);

        AuditTrail::log(
            Auth::user(),
            'StockTransaction',
            $transaction->id,
            'approve',
            ['status' => 'pending'],
            ['status' => 'approved']
        );

        $this->confirmApproveId = null;
        session()->flash('success', 'Transaksi berhasil di-approve');
    }

    public function showRejectForm($transactionId)
    {
        $this->rejectTransactionId = $transactionId;
        $this->showRejectForm = true;
        $this->rejectReason = '';
    }

    public function reject()
    {
        $this->validate();

        $transaction = StockTransaction::findOrFail($this->rejectTransactionId);

        // Business logic will be implemented in Phase 7b
        // For now, just update status
        $transaction->update([
            'status' => 'rejected',
            'notes' => $transaction->notes . ' | REJECTED: ' . $this->rejectReason,
        ]);

        AuditTrail::log(
            Auth::user(),
            'StockTransaction',
            $transaction->id,
            'reject',
            ['status' => 'pending'],
            ['status' => 'rejected', 'reject_reason' => $this->rejectReason]
        );

        $this->showRejectForm = false;
        $this->rejectTransactionId = null;
        $this->rejectReason = '';

        session()->flash('success', 'Transaksi berhasil di-reject');
    }

    public function cancelReject()
    {
        $this->showRejectForm = false;
        $this->rejectTransactionId = null;
        $this->rejectReason = '';
    }

    public function cancelApprove()
    {
        $this->confirmApproveId = null;
    }

    public function getTransactionTypeLabel($type)
    {
        return match($type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Adjustment',
            default => $type,
        };
    }

    public function getTransactionTypeBadgeClass($type)
    {
        return match($type) {
            'in' => 'bg-green-100 text-green-800',
            'out' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getReasonLabel($reason)
    {
        return match($reason) {
            'penjualan' => 'Penjualan',
            'rusak' => 'Rusak',
            'adjustment' => 'Adjustment',
            'lainnya' => 'Lainnya',
            default => $reason,
        };
    }

    public function timeAgo($date)
    {
        return \Carbon\Carbon::parse($date)->diffForHumans();
    }
}