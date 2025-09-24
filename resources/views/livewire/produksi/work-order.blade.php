 <div>
     {{-- Notifikasi Web Push --}}
     {{-- @auth
 <div class="mt-4" wire:ignore>
   <button id="btnPush" class="px-3 py-1.5 rounded bg-indigo-600 text-white text-sm">Aktifkan Notifikasi</button>
   <button id="btnUnpush" class="px-3 py-1.5 rounded bg-gray-200 text-gray-700 text-sm hidden">Matikan Notifikasi</button>
   <small id="pushStatus" class="ml-2 text-gray-500"></small>
 </div>
 @endauth --}}
     <div class="mx-auto max-w-7xl px-4 " x-data="tabs()" x-init="init()">
         <h1 class="text-xl md:text-2xl font-bold mb-4 text-center md:text-left">Perintah Produksi</h1>

         <!-- Tabs Header -->
         <div class="relative border-b border-gray-300 mb-4">
             <nav class="flex flex-wrap md:flex-nowrap gap-2 justify-center md:justify-start" aria-label="Tabs">
                 <button :class="tabClass('utama')" @click="select('utama')"
                     class="flex items-center gap-2 px-3 py-2 rounded-t-md">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                             d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                     </svg>
                     <span>Giling Utama</span>
                 </button>

                 <button :class="tabClass('tambahan')" @click="select('tambahan')"
                     class="flex items-center gap-2 px-3 py-2 rounded-t-md">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                         <path
                             d="M11 2a1 1 0 011 1v1.07a7.002 7.002 0 015.657 5.657H19a1 1 0 110 2h-1.07A7.002 7.002 0 0112 17.93V19a1 1 0 11-2 0v-1.07A7.002 7.002 0 014.343 9.727H3a1 1 0 110-2h1.07A7.002 7.002 0 0110 4.07V3a1 1 0 011-1z" />
                     </svg>
                     <!-- CHANGED TEXT -->
                     <span>Giling Tambahan Total :</span>
                     <span class="font-semibold" x-text="maxTambahanKe || '-'"></span>

                     <!-- BADGE -->
                     <span x-show="unreadTambahan > 0" x-text="unreadTambahan"
                         class="ml-2 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-red-600 text-white"
                         x-cloak></span>
                 </button>
             </nav>
             <div class="absolute bottom-0 h-0.5 bg-blue-600 transition-all duration-300" :style="underlineStyle()">
             </div>
         </div>

         <!-- Tabs Content -->
         <div class="space-y-6">
             <!-- Tab Utama -->
             <div x-show="active === 'utama'" x-cloak>
                 <div class="flex flex-col md:flex-row items-start md:items-center gap-2 mb-3">
                     <label class="font-semibold text-sm w-full md:w-auto">Tanggal</label>
                     <input type="date" wire:model.live="tanggal"
                         class="border rounded px-3 py-2 text-sm w-full md:w-auto">
                     @if (session('message'))
                         <span class="text-green-600 text-sm">{{ session('message') }}</span>
                     @endif
                 </div>

                 <div class="overflow-x-auto">
                     <table class="min-w-full text-sm border">
                         <thead class="bg-indigo-100 text-indigo-700 text-sm">
                             <tr>
                                 <th class="p-2 border text-left">No</th>
                                 <th class="p-2 border text-left">Produk</th>
                                 <th class="p-2 border text-right">Jumlah Tong</th>
                                 <th class="p-2 border text-right">Standar/Tong</th>
                                 <th class="p-2 border text-right">Konversi (Pcs)</th>
                             </tr>
                         </thead>
                         <tbody class="text-indigo-700">
                             @forelse ($dataUtama as $item)
                                 <tr class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50">
                                     <td class="border text-center">{{ $loop->iteration }}</td>
                                     <td class="border">{{ $item['nama'] }}</td>
                                     <td class="border text-right text-indigo-600 font-semibold">
                                         {{ number_format((float) ($item['total_utama'] ?? 0), 1, ',', '.') }}
                                     </td>
                                     <td class="border text-right">
                                         {{ number_format((float) ($item['patokan'] ?? 0), 0, ',', '.') }}
                                     </td>
                                     <td class="border text-right">
                                         {{ (int) ($item['konversiutama'] ?? 0) === 0 ? '-' : number_format((int) $item['konversiutama'], 0, ',', '.') }}
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="5" class="p-3 text-center text-gray-500 border">Tidak ada data.
                                     </td>
                                 </tr>
                             @endforelse
                         </tbody>
                         <tfoot>
                             <tr class="bg-Black-500 font-semibold">
                                 <td class="p-2 border text-center" colspan="2">Total</td>
                                 <td class="p-2 border text-right text-indigo-700">
                                     {{ (float) $sumTongUtama == 0.0 ? '-' : number_format((float) $sumTongUtama, 1, ',', '.') }}
                                 </td>
                                 <td class="p-2 border text-right">—</td>
                                 <td class="p-2 border text-right">
                                     {{ (int) $sumPcsUtama === 0 ? '-' : number_format((int) $sumPcsUtama, 0, ',', '.') }}
                                 </td>
                             </tr>
                         </tfoot>
                     </table>
                 </div>
             </div>

             <!-- Tab Tambahan -->
             <!-- Tab Tambahan -->
             <div x-show="active === 'tambahan'" x-cloak>
                 <div class="flex flex-col md:flex-row items-start md:items-center gap-2 mb-3">
                     <label class="font-semibold text-sm w-full md:w-auto">Tanggal</label>
                     <input type="date" wire:model.live="tanggal"
                         class="border rounded px-3 py-2 text-sm w-full md:w-auto">
                     @if (session('message'))
                         <span class="text-green-600 text-sm">{{ session('message') }}</span>
                     @endif
                 </div>

                 @if ($maxTambahanKe === 0)
                     <div class="p-3 text-center text-gray-500 border rounded">Belum ada giling tambahan.</div>
                 @else
                     @for ($ke = 1; $ke <= $maxTambahanKe; $ke++)
                         @php
                             $blok = $perKe[$ke] ?? ['detail' => [], 'sumTong' => 0.0, 'sumPcs' => 0];
                             $detail = $blok['detail'] ?? [];
                             $sumTong = (float) ($blok['sumTong'] ?? 0.0);
                             $sumPcs = (int) ($blok['sumPcs'] ?? 0);
                         @endphp

                         <div class="mt-6">
                             <h3 class="text-base md:text-lg font-semibold mb-2 text-indigo-700">
                                 Tambahan {{ $ke }}
                             </h3>

                             <div class="overflow-x-auto">
                                 <table class="min-w-full text-sm border">
                                     <thead class="bg-indigo-100 text-indigo-700 text-sm">
                                         <tr>
                                             <th class="p-2 border text-left">No</th>
                                             <th class="p-2 border text-left">Produk</th>
                                             <th class="p-2 border text-right">Jumlah Tong</th>
                                             <th class="p-2 border text-right">Standar/Tong</th>
                                             <th class="p-2 border text-right">Konversi (Pcs)</th>
                                         </tr>
                                     </thead>
                                     <tbody class="text-indigo-700">
                                         @forelse ($detail as $idx => $item)
                                             <tr class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50">
                                                 <td class="border text-center">{{ $idx + 1 }}</td>
                                                 <td class="border">{{ $item['nama'] }}</td>
                                                 <td class="border text-right text-indigo-600 font-semibold">
                                                     {{ number_format((float) ($item['qty_tong'] ?? 0), 1, ',', '.') }}
                                                 </td>
                                                 <td class="border text-right">
                                                     {{ number_format((float) ($item['patokan'] ?? 0), 0, ',', '.') }}
                                                 </td>
                                                 <td class="border text-right">
                                                     {{ (int) ($item['konversi'] ?? 0) === 0 ? '-' : number_format((int) $item['konversi'], 0, ',', '.') }}
                                                 </td>
                                             </tr>
                                         @empty
                                             <tr>
                                                 <td colspan="5" class="p-3 text-center text-gray-500 border">Tidak
                                                     ada data.</td>
                                             </tr>
                                         @endforelse
                                     </tbody>
                                     <tfoot>
                                         <tr class="bg-Black-500 font-semibold">
                                             <td class="p-2 border text-center" colspan="2">Total</td>
                                             <td class="p-2 border text-right text-indigo-700">
                                                 {{ $sumTong == 0.0 ? '-' : number_format($sumTong, 1, ',', '.') }}
                                             </td>
                                             <td class="p-2 border text-right">—</td>
                                             <td class="p-2 border text-right">
                                                 {{ $sumPcs === 0 ? '-' : number_format($sumPcs, 0, ',', '.') }}
                                             </td>
                                         </tr>
                                     </tfoot>
                                 </table>
                             </div>
                         </div>
                     @endfor
                 @endif
             </div>

         </div>
     </div>
 </div>
 {{-- Taruh semua <script> sebagai anak dari wrapper yg sama, atau pindah ke layout pakai @push --}}
 {{-- @auth
    <script>
    (async () => {
      try {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        const reg = await navigator.serviceWorker.register('/service-worker.v1.js?v=1');
        const toUint8=(b64)=>{const p='='.repeat((4-b64.length%4)%4);const s=(b64+p).replace(/-/g,'+').replace(/_/g,'/');const r=atob(s);const a=new Uint8Array(r.length);for(let i=0;i<r.length;i++)a[i]=r.charCodeAt(i);return a;}
        const $on=document.getElementById('btnPush'),$off=document.getElementById('btnUnpush'),$st=document.getElementById('pushStatus');
        async function updateButtons(){const sub=await reg.pushManager.getSubscription();$on?.classList.toggle('hidden',!!sub);$off?.classList.toggle('hidden',!sub);$st.textContent=(Notification.permission==='denied')?'Izin notifikasi ditolak di browser.':(sub?'Notifikasi aktif.':'');}
        $on?.addEventListener('click', async () => {
          const perm = await Notification.requestPermission(); if (perm!=='granted') return alert('Izin ditolak.');
          const sub = await reg.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:toUint8("{{ config('webpush.vapid.public_key') }}")});
          await fetch('/webpush/subscribe',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(sub)});
          alert('Notifikasi diaktifkan.'); updateButtons();
        });
        $off?.addEventListener('click', async () => {
          const sub = await reg.pushManager.getSubscription(); if(!sub) return;
          await fetch('/webpush/unsubscribe',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({endpoint:sub.endpoint})});
          await sub.unsubscribe(); alert('Notifikasi dimatikan.'); updateButtons();
        });
        updateButtons();
      } catch(e){ console.error(e); alert('Gagal inisialisasi notifikasi, cek console.'); }
    })();
    </script>
    @endauth --}}
 <!-- Alpine.js Tabs -->
 <script>
     function tabs() {
         return {
             active: 'utama',
             positions: {
                 utama: {
                     left: '0%',
                     width: '50%'
                 },
                 tambahan: {
                     left: '50%',
                     width: '50%'
                 },
             },

             // === NEW: state notif ===
             totalTambahan: {{ $totalTambahanCount ?? 0 }},
             unreadTambahan: 0,
             notifKey: @js($notifKey ?? 'perintah_none'),
             // NEW: ke maksimum (server-rendered default)
             maxTambahanKe: {{ $maxTambahanKe ?? 0 }},

             init() {
                 // restore tab
                 const saved = localStorage.getItem('tab-stok');
                 if (['utama', 'tambahan'].includes(saved)) this.active = saved;
                 if (location.hash === '#utama') this.active = 'utama';
                 if (location.hash === '#tambahan') this.active = 'tambahan';

                 // hitung unread awal
                 this.recomputeUnread();

                 // dengarkan event dari Livewire/render
                 window.addEventListener('tambahan-count', (e) => {
                     const {
                         total,
                         key
                     } = e.detail || {};
                     if (typeof total === 'number') this.totalTambahan = total;
                     if (typeof key === 'string') this.notifKey = key;
                     if (typeof maxKe === 'number') this.maxTambahanKe = maxKe;
                     this.recomputeUnread();
                 });
             },

             select(key) {
                 this.active = key;
                 localStorage.setItem('tab-stok', key);
                 history.replaceState(null, '', '#' + key);

                 // Kalau user membuka tab tambahan, tandai "sudah dilihat"
                 if (key === 'tambahan') {
                     this.markSeen();
                 }
             },

             tabClass(key) {
                 return [
                     'relative px-4 py-2 text-sm md:text-base font-medium rounded-t-md transition-colors',
                     'flex items-center justify-center gap-2',
                     this.active === key ?
                     'text-white bg-indigo-600' :
                     'text-gray-600 hover:text-indigo-700 hover:bg-indigo-100'
                 ].join(' ');
             },

             underlineStyle() {
                 const pos = this.positions[this.active];
                 return `left:${pos.left}; width:${pos.width};`;
             },

             // === NEW: helpers notif ===
             storageKey() {
                 return `seen_tambahan_count:${this.notifKey}`;
             },
             getSeen() {
                 const raw = localStorage.getItem(this.storageKey());
                 const n = Number(raw);
                 return Number.isFinite(n) ? n : 0;
             },
             setSeen(n) {
                 localStorage.setItem(this.storageKey(), String(n));
             },
             recomputeUnread() {
                 const seen = this.getSeen();
                 this.unreadTambahan = Math.max(this.totalTambahan - seen, 0);
             },
             markSeen() {
                 // Saat tab tambahan dibuka, anggap semua sudah dilihat
                 this.setSeen(this.totalTambahan);
                 this.recomputeUnread();
             },
         }
     }
 </script>
