<style>
    /* === Glow Efek CSS Manual === */

    /* Glow teks Dashboard */
    .text-glow {
      color: #fff;
      text-shadow:
        0 0 6px rgba(255, 255, 255, 0.7),
        0 0 12px rgba(59, 130, 246, 0.7),
        0 0 20px rgba(99, 102, 241, 0.6);
      animation: text-glow 3s ease-in-out infinite;
    }
    @keyframes text-glow {
      0%, 100% {
        text-shadow:
          0 0 6px rgba(255, 255, 255, 0.7),
          0 0 12px rgba(59, 130, 246, 0.7),
          0 0 20px rgba(99, 102, 241, 0.6);
      }
      50% {
        text-shadow:
          0 0 10px rgba(255, 255, 255, 1),
          0 0 20px rgba(59, 130, 246, 0.9),
          0 0 28px rgba(99, 102, 241, 0.8);
      }
    }

    /* Glow box user */
    .glow-ring {
      animation: ring-glow 3s ease-in-out infinite;
    }
    @keyframes ring-glow {
      0%, 100% {
        box-shadow: 0 0 6px rgba(59,130,246,0.5),
                    0 0 12px rgba(99,102,241,0.4);
      }
      50% {
        box-shadow: 0 0 12px rgba(59,130,246,0.9),
                    0 0 22px rgba(99,102,241,0.6);
      }
    }

    /* Glow untuk card (biru, hijau, kuning) */
    .glow-blue { animation: glow-blue 3s ease-in-out infinite; }
    @keyframes glow-blue {
      0%,100% { box-shadow: 0 0 8px rgba(59,130,246,0.5),0 0 16px rgba(59,130,246,0.4); }
      50% { box-shadow: 0 0 18px rgba(59,130,246,0.9),0 0 30px rgba(59,130,246,0.6); }
    }
    .glow-green { animation: glow-green 3s ease-in-out infinite; }
    @keyframes glow-green {
      0%,100% { box-shadow: 0 0 8px rgba(34,197,94,0.5),0 0 16px rgba(34,197,94,0.4); }
      50% { box-shadow: 0 0 18px rgba(34,197,94,0.9),0 0 30px rgba(34,197,94,0.6); }
    }
    .glow-yellow { animation: glow-yellow 3s ease-in-out infinite; }
    @keyframes glow-yellow {
      0%,100% { box-shadow: 0 0 8px rgba(234,179,8,0.5),0 0 16px rgba(234,179,8,0.4); }
      50% { box-shadow: 0 0 18px rgba(234,179,8,0.9),0 0 30px rgba(234,179,8,0.6); }
    }
    </style>

    <!-- === HEADER === -->
    <header class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 flex items-center justify-between rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-glow">Dashboard</h1>

        <div class="flex items-center space-x-4">
          <div class="flex items-center bg-white rounded-full px-3 py-2 shadow-lg glow-ring">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 text-blue-500"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A8.963 8.963 0 0112 15c2.21 0 4.216.805 5.879 2.137M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>

            <div class="ml-2 leading-tight">
              <div class="text-gray-800 font-medium text-sm">
                Hi, {{ auth()->user()->name }}
              </div>
              <div class="text-[11px] text-gray-500">
                {{ auth()->user()->wilayah?->nama_wilayah ?? '-' }}
                •
                {{ auth()->user()->area?->nama_area ?? '-' }}
              </div>
            </div>
          </div>
        </div>
      </header>


    <!-- === CONTENT / CARD === -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <div class="bg-white rounded-2xl p-6 flex items-center space-x-4 glow-blue">
        <div class="bg-blue-100 text-blue-500 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m0 0L3 10m6-7l6 7"/>
          </svg>
        </div>
        <div>
          <h2 class="text-sm text-gray-500">Total Barang</h2>
          <div class="text-2xl font-bold text-gray-800">{{$jumlahBarang}}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 flex items-center space-x-4 glow-green">
        <div class="bg-green-100 text-green-500 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"/>
          </svg>
        </div>
        <div>
          <h2 class="text-sm text-gray-500">Supplier Aktif</h2>
          <div class="text-2xl font-bold text-gray-800">{{$jumlahSupplier}}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 flex items-center space-x-4 glow-yellow">
        <div class="bg-yellow-100 text-yellow-500 p-3 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-sm text-gray-500">Transaksi Hari Ini</h2>
          <div class="text-2xl font-bold text-gray-800">{{$qtyTransaksi}} -> Rp. {{ number_format($jumlahTransaksi, 0, ',', '.') }}</div>
        </div>
      </div>
    </div>
