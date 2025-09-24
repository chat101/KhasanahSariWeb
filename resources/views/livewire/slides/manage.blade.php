{{-- resources/views/livewire/slides/manage.blade.php --}}
<div class="space-y-8">

    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Master Slides</h1>
      <p class="text-sm text-gray-500 mt-1">Kelola banner/slider untuk aplikasi & situs. Rekomendasi ukuran: <span class="font-medium">1200×600px</span>, JPG/PNG ≤ 4MB.</p>
    </div>

    <form wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      {{-- Kiri: Form teks --}}
      <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Judul</label>
          <input type="text" wire:model="title"
                 class="mt-1 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"/>
          @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Link URL (opsional)</label>
          <input type="url" placeholder="https://..." wire:model="link_url"
                 class="mt-1 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"/>
          @error('link_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Urutan (position)</label>
          <input type="number" wire:model="position" min="0"
                 class="mt-1 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"/>
          @error('position') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 mt-6">
          <input id="is_active" type="checkbox" wire:model="is_active"
                 class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
          <label for="is_active" class="text-sm text-gray-700">Aktif</label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Mulai Tayang</label>
          <input type="datetime-local" wire:model="starts_at"
                 class="mt-1 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"/>
          @error('starts_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Akhir Tayang</label>
          <input type="datetime-local" wire:model="ends_at"
                 class="mt-1 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"/>
          @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Kanan: Upload + Preview --}}
      <div x-data="{ preview: null }" class="lg:col-span-1">
        <label class="block text-sm font-medium text-gray-700">
          Gambar {{ $slideId ? '(biarkan kosong jika tidak ganti)' : '' }}
        </label>

        {{-- Dropzone style --}}
        <label for="image"
               class="mt-1 block cursor-pointer rounded-2xl border-2 border-dashed border-gray-300 p-5 hover:border-indigo-400 hover:bg-indigo-50/40 transition">
          <div class="flex items-center gap-4">
            <div class="shrink-0 rounded-xl bg-gray-100 p-3">
              {{-- icon --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h7.5A2.25 2.25 0 0 1 15 8.25V9m0 7.5H8.25A2.25 2.25 0 0 1 6 14.25v-3A2.25 2.25 0 0 1 8.25 9H21"/>
              </svg>
            </div>
            <div>
              <p class="text-sm">
                <span class="font-medium text-indigo-700">Klik untuk unggah</span>
                <span class="text-gray-500"> atau seret & jatuhkan</span>
              </p>
              <p class="text-xs text-gray-500">JPG/PNG ≤ 4MB · Disarankan 1200×600px</p>
            </div>
          </div>
          <input id="image" type="file" accept="image/*" wire:model="image" class="sr-only"
                 @change="if($event.target.files[0]){ preview = URL.createObjectURL($event.target.files[0]); }">
        </label>

        {{-- Progress upload --}}
        <div class="mt-3" wire:loading wire:target="image">
          <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
            <div class="h-2 w-2/3 animate-pulse bg-indigo-500"></div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Mengunggah gambar…</p>
        </div>

        @error('image') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

        {{-- Preview baru & yang sekarang --}}
        <div class="mt-4 grid grid-cols-2 gap-3">
          <div>
            <div class="text-xs font-medium text-gray-600 mb-1">Preview baru</div>
            <div class="aspect-[2/1] w-full overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
              <template x-if="preview">
                <img :src="preview" alt="Preview baru" class="h-full w-full object-cover">
              </template>
              <template x-if="!preview">
                <div class="h-full w-full grid place-items-center text-xs text-gray-400">Belum dipilih</div>
              </template>
            </div>
          </div>

          <div>
            <div class="text-xs font-medium text-gray-600 mb-1">Saat ini</div>
            <div class="aspect-[2/1] w-full overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
              @if($existing_image_path)
                <img src="{{ Storage::url($existing_image_path) }}" alt="Gambar saat ini"
                     class="h-full w-full object-cover">
              @else
                <div class="h-full w-full grid place-items-center text-xs text-gray-400">Tidak ada</div>
              @endif
            </div>
          </div>
        </div>

        {{-- Tips kecil --}}
        <p class="mt-3 text-xs text-gray-500">
          Tip: pakai rasio 2:1 agar tidak terpotong di slider.
        </p>
      </div>

      {{-- Tombol aksi --}}
      <div class="lg:col-span-3 flex flex-wrap items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center rounded-2xl bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
          Simpan
        </button>
        <button type="button" wire:click="create"
                class="inline-flex items-center rounded-2xl border px-4 py-2 text-gray-700 hover:bg-gray-50">
          Baru
        </button>

        @if(session()->has('message'))
          <span class="text-sm text-green-600">{{ session('message') }}</span>
        @endif
      </div>
    </form>

    {{-- Tabel daftar --}}
    <div class="overflow-x-auto rounded-2xl border">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left">
            <th class="px-4 py-3 font-semibold text-gray-700">ID</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Preview</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Judul</th>
            <th class="px-4 py-3 font-semibold text-gray-700">URL</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Pos</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Aktif</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach($slides as $s)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $s->id }}</td>
              <td class="px-4 py-3">
                <img src="{{ $s->url }}" class="h-12 w-24 rounded-lg object-cover ring-1 ring-gray-200">
              </td>
              <td class="px-4 py-3">{{ $s->title }}</td>
              <td class="px-4 py-3 max-w-[260px]">
                <a href="{{ $s->link_url }}" target="_blank"
                   class="block truncate text-indigo-600 hover:underline">{{ $s->link_url }}</a>
              </td>
              <td class="px-4 py-3">{{ $s->position }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs
                  {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                  {{ $s->is_active ? 'Ya' : 'Tidak' }}
                </span>
              </td>
              <td class="px-4 py-3 space-x-2">
                <button wire:click="edit({{ $s->id }})"
                        class="rounded-xl border px-3 py-1 hover:bg-gray-50">Edit</button>
                <button wire:click="delete({{ $s->id }})"
                        onclick="return confirm('Hapus slide ini?')"
                        class="rounded-xl border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">Hapus</button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
