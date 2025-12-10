<div>
    <div class="p-6 max-w-xl mx-auto">

        <h2 class="text-xl font-bold mb-4">Edit Bank</h2>

        <form wire:submit.prevent="update" class="space-y-4">

            <div>
                <label class="block mb-1">Nama Bank</label>
                <input type="text" wire:model="nama_bank" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Nomor Rekening</label>
                <input type="text" wire:model="nomor_rekening" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Atas Nama</label>
                <input type="text" wire:model="atas_nama" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Saldo Awal</label>
                <input type="number" wire:model="saldo_awal" class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>

</div>
