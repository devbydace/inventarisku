<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-6">Laporan Mutasi Stok</h1>

                    <!-- Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-green-50 dark:bg-green-900 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Masuk</p>
                            <p class="text-2xl font-bold text-green-600">{{ $summary['total_in'] }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Keluar</p>
                            <p class="text-2xl font-bold text-red-600">{{ $summary['total_out'] }}</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Adjustment</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $summary['total_adjustment'] }}</p>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                                <input type="date" wire:model.live="date_from" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                                <input type="date" wire:model.live="date_to" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis</label>
                                <select wire:model.live="type" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                                    <option value="">Semua</option>
                                    <option value="in">Stok Masuk</option>
                                    <option value="out">Stok Keluar</option>
                                    <option value="adjustment">Adjustment</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button wire:click="resetFilters" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                                    Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($transactions->count() == 0)
                        <div class="text-center py-12">
                            <p class="text-gray-500">Tidak ada mutasi stok untuk filter yang dipilih</p>
                        </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Approver</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $this->getTypeBadgeClass($transaction->type) }}">
                                                {{ $this->getTypeLabel($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $transaction->product->name ?? '-' }}
                                            <div class="text-xs text-gray-500">{{ $transaction->product->sku ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $transaction->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $this->getStatusBadgeClass($transaction->status) }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $transaction->user->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $transaction->approval->user->name ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>