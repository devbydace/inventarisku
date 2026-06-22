<?php

namespace App\Livewire\Approval;

use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\ApprovalService;
use App\Models\AuditTrail;

class Index extends Component
{
    public $showRejectForm = false;
    public $rejectTransactionId = null;
    public $rejectReason = '';
    public $confirmApproveId = null;
    protected $approvalService;

    protected $rules = [
        'rejectReason' => 'required|string|min:10',
    ];

    protected $messages = [
        'rejectReason.required' => 'Alasan reject harus diisi',
        'rejectReason.min' => 'Alasan reject harus minimal 10 karakter',
    ];

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

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
        try {
            $transaction = StockTransaction::findOrFail($transactionId);
            
            $this->approvalService->approve($transaction, Auth::user());

            $this->confirmApproveId = null;
            session()->flash('success', 'Transaksi berhasil di-approve');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function showRejectForm($transactionId)
    {
        $this->rejectTransactionId = $transactionId;
        $this->showRejectForm = true;
        $this->rejectReason = '';
    }

    public function reject()
    {
        try {
            $this->validate();

            $transaction = StockTransaction::findOrFail($this->rejectTransactionId);
            
            $this->approvalService->reject($transaction, Auth::user(), $this->rejectReason);

            $this->showRejectForm = false;
            $this->rejectTransactionId = null;
            $this->rejectReason = '';

            session()->flash('success', 'Transaksi berhasil di-reject');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
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