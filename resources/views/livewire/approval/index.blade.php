<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Approval Transaksi</h1>
                        @if($transactions->count() > 0)
                            <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $transactions->count() }} Pending
                            </span>
                        @endif
                    </div>

                    @if (session()->has('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($transactions->count() == 0)
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada transaksi yang menunggu approval</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua transaksi sudah diproses.</p>
                        </div>
                    @else

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Jenis
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Barang
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Jumlah
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Info Tambahan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $this->getTransactionTypeBadgeClass($transaction->type) }}">
                                                {{ $this->getTransactionTypeLabel($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $transaction->product->name ?? 'Unknown' }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    SKU: {{ $transaction->product->sku ?? '-' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $transaction->quantity }}
                                            @if($transaction->product && $transaction->product->unit)
                                                {{ $transaction->product->unit->abbreviation }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if($transaction->type == 'in' && $transaction->supplier)
                                                <div class="text-xs">
                                                    <strong>Supplier:</strong> {{ $transaction->supplier->name }}
                                                </div>
                                            @endif
                                            @if($transaction->type == 'out' && $transaction->reason)
                                                <div class="text-xs">
                                                    <strong>Alasan:</strong> {{ $this->getReasonLabel($transaction->reason) }}
                                                </div>
                                            @endif
                                            @if($transaction->reference_no)
                                                <div class="text-xs">
                                                    <strong>Ref:</strong> {{ $transaction->reference_no }}
                                                </div>
                                            @endif
                                            @if($transaction->notes)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ Str::limit($transaction->notes, 50) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div>{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="text-xs text-gray-500">{{ $this->timeAgo($transaction->created_at) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $transaction->user->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($confirmApproveId == $transaction->id)
                                                <div class="bg-yellow-50 border border-yellow-200 rounded p-2">
                                                    <p class="text-xs mb-2">Yakin ingin approve transaksi ini?</p>
                                                    <div class="flex gap-1">
                                                        <button wire:click="approve({{ $transaction->id }})" 
                                                                class="bg-green-500 hover:bg-green-700 text-white text-xs px-2 py-1 rounded">
                                                            Ya, Approve
                                                        </button>
                                                        <button wire:click="cancelApprove" 
                                                                class="bg-gray-500 hover:bg-gray-700 text-white text-xs px-2 py-1 rounded">
                                                            Batal
                                                        </button>
                                                    </div>
                                                </div>
                                            @elseif($showRejectForm && $rejectTransactionId == $transaction->id)
                                                <div class="bg-red-50 border border-red-200 rounded p-2">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Alasan Reject (min 10 karakter)
                                                    </label>
                                                    <textarea wire:model="rejectReason" 
                                                              rows="2" 
                                                              class="text-xs border border-gray-300 rounded p-1 w-full"
                                                              placeholder="Masukkan alasan reject..."></textarea>
                                                    @error('rejectReason')
                                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                    @enderror
                                                    <div class="flex gap-1 mt-1">
                                                        <button wire:click="reject" 
                                                                class="bg-red-500 hover:bg-red-700 text-white text-xs px-2 py-1 rounded">
                                                            Reject
                                                        </button>
                                                        <button wire:click="cancelReject" 
                                                                class="bg-gray-500 hover:bg-gray-700 text-white text-xs px-2 py-1 rounded">
                                                            Batal
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <button wire:click="confirmApprove({{ $transaction->id }})" 
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2"
                                                        title="Approve">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <button wire:click="showRejectForm({{ $transaction->id }})" 
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Reject">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>