<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-6">Penyesuaian Stok</h1>

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

                    <form wire:submit="store">
                        <div class="mb-4">
                            <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Barang <span class="text-red-500">*</span>
                            </label>
                            <select id="product_id" 
                                    wire:model.live="product_id" 
                                    class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('product_id') border-red-500 @enderror">
                                <option value="">-- Pilih Barang --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $product->current_stock }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror

                            @if($selectedProduct)
                                <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900 rounded">
                                    <p class="text-sm"><strong>Stok Sistem Saat Ini:</strong> {{ $selectedProduct->current_stock }} {{ $selectedProduct->unit->abbreviation ?? '' }}</p>
                                    <p class="text-sm"><strong>Kategori:</strong> {{ $selectedProduct->category->name ?? '-' }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="physical_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Stok Fisik <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="physical_stock" 
                                   wire:model="physical_stock" 
                                   min="0"
                                   class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('physical_stock') border-red-500 @enderror">
                            @error('physical_stock')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alasan <span class="text-red-500">*</span>
                            </label>
                            <textarea id="reason" 
                                      wire:model="reason" 
                                      rows="4" 
                                      maxlength="500"
                                      class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('reason') border-red-500 @enderror"
                                      placeholder="Jelaskan alasan penyesuaian stok..."></textarea>
                            @error('reason')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-sm text-gray-500 mt-1">{{ strlen($reason) }}/500 karakter (minimal 10)</p>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit
                            </button>
                            <button type="reset" 
                                    wire:click="$refresh"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>