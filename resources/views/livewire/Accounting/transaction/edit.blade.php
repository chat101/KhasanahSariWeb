<div class="p-3 max-w-md mx-auto">

    <div class="bg-gray-800 border border-gray-700 rounded-md p-3 shadow">

        <h2 class="text-base font-semibold text-gray-100 mb-3">
            {{ $formTitle }}
        </h2>

        <form wire:submit.prevent="{{ $formAction }}" class="space-y-2 text-xs">

            {{-- BANK --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Bank</label>
                <select wire:model="bank_id"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
                    <option value="">Pilih Bank</option>
                    @foreach ($banks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bank }}</option>
                    @endforeach
                </select>
            </div>

            {{-- GRID INPUT --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-gray-300 mb-0.5 text-[10px]">Tanggal</label>
                    <input type="date" wire:model="tanggal"
                        class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
                </div>

                <div>
                    <label class="block text-gray-300 mb-0.5 text-[10px]">Tipe</label>
                    <select wire:model="tipe"
                        class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
                        <option value="debit">Debit</option>
                        <option value="kredit">Kredit</option>
                    </select>
                </div>
            </div>

            {{-- KATEGORI --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Kategori</label>
                <select wire:model="category_id"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
                    <option value="">Tidak ada</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            {{-- JUMLAH --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Jumlah</label>
                <input type="number" wire:model="jumlah"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
            </div>

            {{-- REF --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Ref No</label>
                <input type="text" wire:model="ref_no"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
            </div>

            {{-- KETERANGAN --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Keterangan</label>
                <textarea wire:model="keterangan"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs h-14"></textarea>
            </div>

            {{-- BUKTI LAMA --}}
            @if(isset($old_bukti) && $old_bukti)
                <div>
                    <label class="block text-gray-300 mb-0.5 text-[10px]">Bukti Lama</label>
                    <a href="{{ asset('storage/'.$old_bukti) }}"
                       target="_blank"
                       class="text-blue-300 underline text-[10px]">
                        Lihat Bukti
                    </a>
                </div>
            @endif

            {{-- BUKTI BARU --}}
            <div>
                <label class="block text-gray-300 mb-0.5 text-[10px]">Upload Bukti Baru</label>
                <input type="file" wire:model="bukti"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded text-xs">
            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end gap-2 mt-3">

                <a href="{{ route('transaksi.index') }}"
                    class="px-2 py-1 rounded bg-gray-600 hover:bg-gray-700 text-white text-[11px]">
                    Batal
                </a>

                <button
                    class="px-2 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-[11px]">
                    {{ $submitButton }}
                </button>

            </div>

        </form>

    </div>

</div>
