<div>
    <div class="p-6 max-w-xl mx-auto">

        <h2 class="text-xl font-bold mb-4">Tambah Bank</h2>

        <form wire:submit.prevent="save" class="space-y-4">

            <div>
                <label class="block mb-1">Nama Bank</label>
                <input type="text" wire:model="nama_bank" class="w-full border rounded px-3 py-2">
                @error('nama_bank') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Nomor Rekening</label>
                <input type="text" wire:model="nomor_rekening" class="w-full border rounded px-3 py-2">
                @error('nomor_rekening') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Atas Nama</label>
                <input type="text" wire:model="atas_nama" class="w-full border rounded px-3 py-2">
                @error('atas_nama') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Saldo Awal</label>
                <input type="number" wire:model="saldo_awal" class="w-full border rounded px-3 py-2">
                @error('saldo_awal') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        </form>
    </div>

</div>
