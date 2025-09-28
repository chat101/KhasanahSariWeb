<div>
    <div class="min-h-screen bg-gray-900 text-white p-6">
        <h1 class="text-2xl font-bold mb-6">Rekap Mesin & Produk</h1>

        {{-- Notifikasi Livewire --}}
        @if (session('success'))
          <div class="mb-4 bg-green-600/20 border border-green-500 text-green-200 px-4 py-2 rounded">
            {{ session('success') }}
          </div>
        @endif

        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-700">
            <thead class="bg-gray-800">
              <tr>
                <th class="px-4 py-2 text-left">Mesin</th>
                <th class="px-4 py-2 text-left">Kode</th>
                <th class="px-4 py-2 text-left">Kapasitas/Jam</th>
                <th class="px-4 py-2 text-left">Produk</th>
                <th class="px-4 py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($machines as $m)
                <tr class="border-t border-gray-700">
                  <td class="px-4 py-2">{{ $m->nama }}</td>
                  <td class="px-4 py-2">{{ $m->kode ?? '-' }}</td>
                  <td class="px-4 py-2">{{ $m->kapasitas_per_jam ?? '-' }}</td>
                  <td class="px-4 py-2">
                    @if($m->products->isEmpty())
                      <span class="text-gray-400">Belum ada produk</span>
                    @else
                      <ul class="list-disc list-inside space-y-1">
                        @foreach($m->products as $product)
                          <li>{{ $product->nama }}</li>
                        @endforeach
                      </ul>
                    @endif
                  </td>
                  <td class="px-4 py-2 text-right">
                    <button wire:click="$set('machineId', {{ $m->id }})"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded">
                      Edit
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada data mesin.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Modal Edit --}}
        @if($machine)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
                <div class="bg-gray-800 w-full max-w-3xl rounded-xl shadow-xl p-6 relative">
                    <button wire:click="$set('machineId', null)"
                            class="absolute top-3 right-3 text-gray-400 hover:text-white">✖</button>

                    <h2 class="text-xl font-semibold mb-4">Atur Produk untuk Mesin</h2>

                    {{-- Detail Mesin --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-300">Nama Mesin</label>
                            <div class="mt-1 font-medium">{{ $machine->nama }}</div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300">Kode</label>
                            <div class="mt-1 font-medium">{{ $machine->kode ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300">Kapasitas/Jam</label>
                            <div class="mt-1 font-medium">{{ $machine->kapasitas_per_jam ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- Form Update pakai Livewire --}}
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div class="border border-gray-700 rounded-lg p-3 max-h-[50vh] overflow-y-auto">
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($allProducts as $p)
                                    <label class="flex items-center gap-2 bg-gray-700/60 rounded px-2 py-2">
                                        <input type="checkbox"
                                               value="{{ $p->id }}"
                                               wire:model="selectedProducts"
                                               class="accent-blue-500">
                                        <span class="text-white text-sm">{{ $p->nama }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button"
                                    wire:click="$set('machineId', null)"
                                    class="px-4 py-2 rounded bg-gray-600 hover:bg-gray-500">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded bg-blue-600 hover:bg-blue-700">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
