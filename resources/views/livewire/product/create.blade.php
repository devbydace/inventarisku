<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-6">Tambah Produk</h1>

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
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Produk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   wire:model="name" 
                                   class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                SKU <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="sku" 
                                   wire:model="sku" 
                                   class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('sku') border-red-500 @enderror">
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id" 
                                    wire:model="category_id" 
                                    class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('category_id') border-red-500 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Satuan <span class="text-red-500">*</span>
                            </label>
                            <select id="unit_id" 
                                    wire:model="unit_id" 
                                    class="border border-gray-300 rounded-md px-4 py-2 w-full md:w-96 @error('unit_id') border-red-500 @enderror">
                                <option value="">-- Pilih Satuan --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan
                            </button>
                            <a href="{{ route('admin.products.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>