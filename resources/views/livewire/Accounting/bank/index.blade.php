<div>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-4">Daftar Bank</h2>

        <div class="mb-4 flex justify-between">
            <input type="text" wire:model.live="search" class="border rounded px-3 py-2"
                placeholder="Cari bank atau rekening...">

            <a href="{{ route('bank.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded">Tambah Bank</a>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border">
            <thead class="bg-gray-100 text-black">
                <tr>
                    <th class="p-2 border">Bank</th>
                    <th class="p-2 border">No Rekening</th>
                    <th class="p-2 border">Atas Nama</th>
                    <th class="p-2 border">Saldo Awal</th>
                    <th class="p-2 border">Saldo Berjalan</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($banks as $bank)
                    <tr>
                        <td class="border p-2">{{ $bank->nama_bank }}</td>
                        <td class="border p-2">{{ $bank->nomor_rekening }}</td>
                        <td class="border p-2">{{ $bank->atas_nama }}</td>
                        <td class="border p-2">Rp {{ number_format($bank->saldo_awal, 0, ',', '.') }}</td>
                        <td class="border p-2">Rp {{ number_format($bank->saldo_akhir, 0, ',', '.') }}</td>
                        <td class="border p-2">
                            <a href="{{ route('bank.edit', $bank->id) }}"
                                class="text-blue-600">Edit</a> |
                            <button wire:click="delete({{ $bank->id }})"
                                class="text-red-600"
                                onclick="return confirm('Hapus bank ini?')">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-3 text-center">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
