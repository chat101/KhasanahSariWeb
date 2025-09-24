{{-- resources/views/livewire/stok/penyesuaian-stok.blade.php --}}
<div class="space-y-4">
    <div class="flex items-center gap-3">
      <label class="font-semibold">Tanggal</label>
      <input type="date" wire:model.live="tanggal" class="border rounded px-2 py-1">
      @if (session('message')) <span class="text-green-600">{{ session('message') }}</span> @endif
    </div>

    <div class="overflow-auto">
      <table class="min-w-full border">
        <thead class="bg-black-500">
          <tr>
            <th class="p-2 border">Produk</th>
            <th class="p-2 border text-right">Stok Sistem (akhir berjalan)</th>
            <th class="p-2 border text-right">Stok Real</th>
            <th class="p-2 border text-right">Selisih (Real − Sistem)</th>
            <th class="p-2 border">Alasan</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($rows as $r)
            @php
              $real = $counted[$r['id']] ?? null;
              $selisih = ($real === null || $real==='') ? 0 : ((float)$real - (float)$r['system']);
            @endphp
            <tr>
              <td class="p-2 border">{{ $r['nama'] }}</td>
              <td class="p-2 border text-right">{{ number_format($r['system'], 3) }}</td>
              <td class="p-2 border text-right">
                <input type="number" step="0.001" wire:model.lazy="counted.{{ $r['id'] }}"
                       class="w-32 border rounded px-2 py-1 text-right">
              </td>
              <td class="p-2 border text-right {{ $selisih==0?'':'font-semibold' }}">
                {{ number_format($selisih, 3) }}
              </td>
              <td class="p-2 border">
                <input type="text" wire:model.lazy="alasan.{{ $r['id'] }}"
                       class="w-64 border rounded px-2 py-1">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan penyesuaian</button>
  </div>
