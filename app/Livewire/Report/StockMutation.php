<?php

namespace App\Livewire\Report;

use App\Models\StockTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class StockMutation extends Component
{
    use WithPagination;

    public $date_from = '';
    public $date_to = '';
    public $type = '';

    public function render()
    {
        $query = StockTransaction::with(['product', 'user', 'approval.user'])
            ->whereIn('status', ['pending', 'approved']);

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(50);

        // Summary
        $summaryQuery = StockTransaction::whereIn('status', ['approved']);
        if ($this->date_from) {
            $summaryQuery->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $summaryQuery->whereDate('created_at', '<=', $this->date_to);
        }
        $summary = $summaryQuery->selectRaw("
            SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as total_out,
            SUM(CASE WHEN type = 'adjustment' THEN quantity ELSE 0 END) as total_adjustment
        ")->first();

        $summaryData = [
            'total_in' => $summary->total_in ?? 0,
            'total_out' => $summary->total_out ?? 0,
            'total_adjustment' => $summary->total_adjustment ?? 0,
        ];

        return view('livewire.report.stock-mutation', [
            'transactions' => $transactions,
            'summary' => $summaryData,
        ]);
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->date_from = '';
        $this->date_to = '';
        $this->type = '';
        $this->resetPage();
    }

    public function getTypeLabel($type)
    {
        return match ($type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Adjustment',
            default => $type,
        };
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'approved' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTypeBadgeClass($type)
    {
        return match ($type) {
            'in' => 'bg-green-100 text-green-800',
            'out' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}