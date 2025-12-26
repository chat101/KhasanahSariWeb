{{-- resources/views/livewire/slides/manage.blade.php --}}
<div class="space-y-8">

    <div class="bg-white rounded-xl shadow p-4 flex items-start justify-between gap-4">
      <div>
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-semibold text-sm">SL</div>
          <div>
            <h1 class="text-lg font-semibold tracking-tight text-gray-900">Master Slides</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola banner/slider untuk aplikasi & situs. <span class="font-medium text-gray-700">Rekomendasi: 1200×600px · JPG/PNG · ≤ 4MB</span></p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button type="submit" form="slides-form"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
          Simpan
        </button>
        <button type="button" wire:click="create"
                class="inline-flex items-center rounded-md border px-3 py-2 text-gray-700 hover:bg-gray-50 text-sm">
          Baru
        </button>
      </div>
    </div>

    <form id="slides-form" wire:submit.prevent="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

      {{-- Kiri: Form teks --}}
      <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
          <input id="title" type="text" wire:model="title" aria-label="Judul slide"
                 class="mt-1 w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="link_url" class="block text-sm font-medium text-gray-700">Link URL (opsional)</label>
          <input id="link_url" type="url" placeholder="https://..." wire:model="link_url" aria-label="Link slide"
                 class="mt-1 w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          @error('link_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="position" class="block text-sm font-medium text-gray-700">Urutan (position)</label>
          <input id="position" type="number" wire:model="position" min="0" aria-label="Posisi slide"
                 class="mt-1 w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          @error('position') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mt-3">
          <label class="flex items-center gap-3 cursor-pointer select-none">
            <div class="relative">
              <input id="is_active" type="checkbox" wire:model="is_active" class="sr-only" />
              <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner transition-colors" :class="{'bg-indigo-600': @entangle('is_active')}" aria-hidden></div>
              <div class="absolute left-0 top-0 w-5 h-5 bg-white rounded-full shadow transform transition-transform" :class="{'translate-x-5': @entangle('is_active')}" aria-hidden></div>
            </div>
            <span class="text-sm text-gray-700">Aktif</span>
          </label>
        </div>

        <div>
          <label for="starts_at" class="block text-sm font-medium text-gray-700">Mulai Tayang</label>
          <input id="starts_at" type="datetime-local" wire:model="starts_at"
                 class="mt-1 w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          @error('starts_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="ends_at" class="block text-sm font-medium text-gray-700">Akhir Tayang</label>
          <input id="ends_at" type="datetime-local" wire:model="ends_at"
                 class="mt-1 w-full rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          @error('ends_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div> 
      </div>

      {{-- Kanan: Upload + Preview --}}
      <div x-data="{ preview: null, name: null, size: null, width: null, height: null, progress: 0, error: null }"
           x-on:livewire-upload-start="progress = 0"
           x-on:livewire-upload-progress="progress = $event.detail.progress"
           x-on:livewire-upload-finish="progress = 100"
           x-on:livewire-upload-error="progress = 0"
           class="lg:col-span-1">

        <label class="block text-sm font-medium text-gray-700">
          Gambar <span class="text-gray-500 text-xs">{{ $slideId ? '(biarkan kosong jika tidak ganti)' : '' }}</span>
        </label>

        {{-- Dropzone style --}}
        <label for="image"
               class="mt-1 block cursor-pointer rounded-2xl border-2 border-dashed border-gray-300 p-5 hover:border-indigo-400 hover:bg-indigo-50/40 transition">
          <div class="flex items-center gap-4">
            <div class="shrink-0 rounded-xl bg-gray-100 p-3">
              {{-- icon --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h7.5A2.25 2.25 0 0 1 15 8.25V9m0 7.5H8.25A2.25 2.25 0 0 1 6 14.25v-3A2.25 2.25 0 0 1 8.25 9H21"/>
              </svg>
            </div>
            <div>
              <p class="text-sm">
                <span class="font-medium text-indigo-700">Klik untuk unggah</span>
                <span class="text-gray-500"> atau seret & jatuhkan</span>
              </p>
              <p class="text-xs text-gray-500">JPG/PNG · Disarankan 1200×600px · <span class="font-medium">Maks 4MB</span></p>
            </div>
          </div>
          <input id="image" type="file" accept="image/*" wire:model="image" class="sr-only"
                 x-on:change="let f = $event.target.files[0]; if(f){ name = f.name; size = f.size; if(size > 4*1024*1024){ error = 'File terlalu besar (max 4MB)'; $event.target.value = ''; preview = null; name = null; size = null; } else { preview = URL.createObjectURL(f); error = null; const i = new Image(); i.onload = () => { width = i.naturalWidth; height = i.naturalHeight; }; i.src = preview; } }">
        </label>

        {{-- Upload progress --}}
        <div class="mt-3" x-show="progress>0" x-cloak>
          <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
            <div :style="`width:${progress}%`" class="h-2 bg-indigo-500 transition-all"></div>
          </div>
          <div class="flex items-center justify-between mt-1">
            <div class="text-xs text-gray-500" x-text="progress + '%'">0%</div>
            <div class="text-xs text-gray-500" x-text="name ? name : ''"></div>
          </div>
        </div>

        <template x-if="error">
          <p class="text-xs text-red-600 mt-2" x-text="error"></p>
        </template>

        @error('image') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

        {{-- Preview baru & yang sekarang --}}
        <div class="mt-4 grid grid-cols-2 gap-3">
          <div>
            <div class="text-xs font-medium text-gray-600 mb-1">Preview baru</div>
            <div class="aspect-[2/1] w-full overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200 relative">
              <template x-if="preview">
                <img :src="preview" alt="Preview baru" class="h-full w-full object-cover">
                <div class="absolute left-2 top-2 bg-black/50 text-white text-xs rounded px-2 py-1"> <span x-text="name"></span> </div>
                <div class="absolute right-2 bottom-2 bg-white/80 text-xs rounded px-2 py-0.5 text-gray-700"> <span x-text="width + '×' + height"></span> </div>
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
                <img src="{{ url('api/slide-file/' . ltrim($existing_image_path, '/')) }}"
                     alt="Gambar saat ini"
                     class="h-full w-full object-cover">
              @else
                <div class="h-full w-full grid place-items-center text-xs text-gray-400">Tidak ada</div>
              @endif
            </div>

            <div class="mt-2 text-xs text-gray-500">
              Tip: pakai rasio 2:1 agar tidak terpotong di slider.
            </div>
          </div>
        </div>
      </div> 

      {{-- Tombol aksi --}}
      <div class="lg:col-span-3 flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" form="slides-form"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
          Simpan
        </button>
        <button type="button" wire:click="create"
                class="inline-flex items-center rounded-md border px-4 py-2 text-gray-700 hover:bg-gray-50 text-sm">
          Baru
        </button>

        @if(session()->has('message'))
          <div class="ml-3 inline-flex items-center rounded-md bg-green-50 px-3 py-1 text-sm text-green-700">{{ session('message') }}</div>
        @endif
      </div>
    </form>

    {{-- Tabel daftar --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
        <tbody class="divide-y divide-gray-100">
          @forelse($slides as $s)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $s->id }}</td>
              <td class="px-4 py-3">
                @if($s->image_url)
                  <img src="{{ $s->image_url }}" class="h-12 w-32 rounded-lg object-cover ring-1 ring-gray-200">
                @else
                  <div class="h-12 w-32 rounded-lg bg-gray-100 grid place-items-center text-[10px] text-gray-400">Tidak ada</div>
                @endif
              </td>
              <td class="px-4 py-3 max-w-[260px] truncate">{{ $s->title }}</td>
              <td class="px-4 py-3 max-w-[260px]">
                <a href="{{ $s->link_url }}" target="_blank" class="block truncate text-indigo-600 hover:underline">{{ $s->link_url }}</a>
              </td>
              <td class="px-4 py-3">{{ $s->position }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $s->is_active ? 'Ya' : 'Tidak' }}</span>
              </td>
              <td class="px-4 py-3 space-x-2">
                <button wire:click="edit({{ $s->id }})" aria-label="Edit slide {{ $s->id }}" title="Edit" class="p-2 rounded-md hover:bg-gray-50 text-gray-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>

                <button wire:click="delete({{ $s->id }})" onclick="return confirm('Hapus slide ini?')" aria-label="Hapus slide {{ $s->id }}" title="Hapus" class="p-2 rounded-md hover:bg-red-50 text-red-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" />
                  </svg>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-10">
                <div class="text-center text-gray-500">
                  <div class="text-lg font-medium">Belum ada slide</div>
                  <div class="text-sm mt-2">Tambahkan slide baru menggunakan formulir di atas.</div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
