<div style="padding:16px; font-size:14px; line-height:1.5; background:#111827; color:#e5e7eb;">
    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div style="padding:8px; background:#064e3b; border:1px solid #10b981; color:#d1fae5; border-radius:6px; font-size:13px; margin-bottom:12px;">
            {{ session('message') }}
        </div>
    @endif

    <!-- Input tanggal -->
    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-bottom:12px;">
        <label for="tanggalInput" style="font-size:13px; font-weight:600; color:#facc15;">Tanggal Produksi:</label>
        <input
            type="date"
            id="tanggalInput"
            wire:model.defer="tanggalProduksi"
            style="
                border:1px solid #374151; border-radius:6px;
                padding:8px 12px; font-size:14px; background:#1f2937; color:#f9fafb;
            "
        />
    </div>

    <!-- Tabel -->
    <div style="overflow-x:auto;">
        <table
            style="
                width:100%; min-width:320px;
                font-size:13px;
                border-collapse:collapse;
                border:1px solid #374151;
                border-radius:8px; overflow:hidden;
                background:#1f2937;
            "
        >
            <thead>
                <tr style="background:#374151; color:#f9fafb; text-align:left;">
                    <th style="display:none;"></th>
                    <th style="border:1px solid #4b5563; padding:10px;">Nama Divisi</th>
                    <th style="border:1px solid #4b5563; padding:10px;">Jam Selesai</th>
                    <th style="border:1px solid #4b5563; padding:10px;">Keterangan</th>
                    <th style="border:1px solid #4b5563; padding:10px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($displayRows as $row)
                    @if ($row['type'] === 'group')
                        <tr style="background:#111827;">
                            <td style="display:none;">—</td>
                            <td style="border:1px solid #4b5563; padding:10px; font-weight:700; text-transform:uppercase;">
                                {{ $row['group_name'] }}
                            </td>
                            <td style="border:1px solid #4b5563; padding:10px;">
                                <input type="time" wire:model.defer="jamSelesaiGroup.{{ $row['gkey'] }}"
                                    style="width:100%; border:1px solid #4b5563; border-radius:4px; padding:4px; background:#1f2937; color:#f9fafb;" />
                            </td>
                            <td style="border:1px solid #4b5563; padding:10px;">
                                <input type="text" placeholder="Keterangan (opsional)"
                                    wire:model.defer="keteranganGroup.{{ $row['gkey'] }}"
                                    style="width:100%; border:none; background:transparent; color:#f9fafb; outline:none;" />
                            </td>
                            <td style="border:1px solid #4b5563; padding:10px; text-align:center;">
                                @php
                                    $saved = $statusTersimpanGroup[$row['gkey']] ?? false;
                                    $members = $groupMembers[$row['gkey']] ?? [];
                                    $savedCount = collect($members)->filter(fn($id)=>($statusTersimpan[$id]??false))->count();
                                @endphp
                                @if ($saved)
                                    <span style="color:#10b981; font-weight:600;">✅ Tersimpan ({{ $savedCount }}/{{ count($members) }})</span>
                                @else
                                    <button wire:click="simpanGrup('{{ $row['gkey'] }}')"
                                        style="background:#2563eb; color:#fff; font-size:12px; padding:6px 12px; border:none; border-radius:4px; cursor:pointer;">
                                        Simpan Grup
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @else
                        <tr style="background:#1f2937;">
                            <td style="display:none;">{{ $row['id'] }}</td>
                            <td style="border:1px solid #4b5563; padding:10px;">{{ $row['nama'] }}</td>
                            <td style="border:1px solid #4b5563; padding:10px;">
                                <input type="time" wire:model.defer="jamSelesai.{{ $row['id'] }}"
                                    style="width:100%; border:1px solid #4b5563; border-radius:4px; padding:4px; background:#111827; color:#f9fafb;" />
                            </td>
                            <td style="border:1px solid #4b5563; padding:10px;">
                                <input type="text" wire:model.defer="keterangan.{{ $row['id'] }}"
                                    style="width:100%; border:none; background:transparent; color:#f9fafb; outline:none;" />
                            </td>
                            <td style="border:1px solid #4b5563; padding:10px; text-align:center;">
                                @if (!empty($statusTersimpan[$row['id']]))
                                    <span style="color:#10b981; font-weight:600;">✅ Tersimpan</span>
                                @else
                                    <button wire:click="simpanPerRow({{ $row['id'] }})"
                                        style="background:#2563eb; color:#fff; font-size:12px; padding:6px 12px; border:none; border-radius:4px; cursor:pointer;">
                                        Simpan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
