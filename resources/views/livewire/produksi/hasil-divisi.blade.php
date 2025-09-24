<div class="p-4 space-y-4 bg-gray-900 text-gray-100 text-sm rounded-lg shadow-lg">
    <div x-data="{ activeTab: 'rekaphasil' }" class="w-full">
        <!-- Tab Navigation -->
        <div class="flex space-x-2 border-b border-gray-300">
            <button @click="activeTab = 'rekaphasil'"
                :class="activeTab === 'rekaphasil'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-red-600'"
                class="px-4 py-2 text-sm">
                Rekap Hasil Divisi
            </button>
            <button @click="activeTab = 'giling'"
                :class="activeTab === 'giling'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Hasil Giling
            </button>

            <button @click="activeTab = 'counter'"
                :class="activeTab === 'counter'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Hasil Counter

            </button>

            <button @click="activeTab = 'poprok'"
                :class="activeTab === 'poprok'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Hasil Poprok
            </button>

            <button @click="activeTab = 'dekor'"
                :class="activeTab === 'dekor'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Hasil Dekor
            </button>
        </div>
        <div class="mt-4 p-4 bg-black rounded-lg shadow border">
            <div x-show="activeTab==='rekaphasil'" x-cloak x-transition.opacity>
                @livewire('produksi.rekap-hasil-divisi', key('rekaphasil'))
              </div>
              <div x-show="activeTab==='giling'" x-cloak x-transition.opacity>
                @livewire('produksi.hasil-giling', key('giling'))
              </div>

              <div x-show="activeTab==='counter'" x-cloak x-transition.opacity>
                @livewire('produksi.hasil-counter', key('counter'))
              </div>

              <div x-show="activeTab==='poprok'" x-cloak x-transition.opacity>
                @livewire('produksi.hasil-poprok', key('poprok'))
              </div>

              <div x-show="activeTab==='dekor'" x-cloak x-transition.opacity>
                @livewire('produksi.hasil-dekor', key('dekor'))
              </div>

        </div>
    </div>
