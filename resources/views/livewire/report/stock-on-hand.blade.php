<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-6">Laporan Stok Saat Ini</h1>

                    <!-- Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Barang</p>
                            <p class="text-2xl font-bold">{{ $totalProducts }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900 p-4 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Low Stock</p>
                            <p class="text-2xl font-bold text-red-600">{{ $lowStockCount }}</p>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select wire:model.live="category_id" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier</label>
                                <select wire:model.live="supplier_id" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                                    <option value="">Semua Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button wire:click="resetFilters" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                                    Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($products->count() == 0)
                        <div class="text-center py-12">
                            <p class="text-gray-500">Tidak ada data stok</p>
                        </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stok Saat Ini</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stok Minimum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $product->category->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $product->suppliers->pluck('name')->implode(', ') ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $product->current_stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $product->minimum_stock }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($product->current_stock < $product->minimum_stock)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Low Stock
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Normal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>