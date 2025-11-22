<div>
    <div class="max-w-3xl mx-auto bg-black shadow-md rounded-xl p-8 mt-8">

        <h2 class="text-2xl font-bold mb-6">Form Tiket Teknisi</h2>

        <form wire:submit.prevent="submit">

            {{-- Kategori --}}
            <label class="font-semibold">Kategori</label>
            <select wire:model="category"
                    class="w-full border rounded-lg p-3 mb-4">
                <option value="">-- Pilih Kategori --</option>
                <option value="Peralatan">Peralatan</option>
                <option value="Listrik">Listrik</option>
                <option value="Sipil">Sipil</option>
                <option value="AC">AC & Pendingin</option>
                <option value="Kebersihan">Kebersihan</option>
                <option value="Lainnya">Lainnya</option>
            </select>
            @error('category') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            {{-- Judul --}}
            <label class="font-semibold">Judul Tiket</label>
            <input type="text" wire:model="title"
                   class="w-full border rounded-lg p-3 mb-4">
            @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            {{-- Deskripsi --}}
            <label class="font-semibold">Deskripsi</label>
            <textarea wire:model="description" rows="4"
                      class="w-full border rounded-lg p-3 mb-4"></textarea>
            @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            {{-- Foto --}}
            <label class="font-semibold">Upload Foto (Opsional)</label>
            <input type="file" wire:model="photos" multiple class="mb-4">

            <div wire:loading wire:target="photos" class="text-blue-500 mb-2">Mengupload foto...</div>

            @error('photos.*') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

            {{-- Button --}}
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700">
                Kirim Tiket
            </button>
        </form>
    </div>

</div>
