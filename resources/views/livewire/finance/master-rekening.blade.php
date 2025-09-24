<div class="p-4 space-y-4 bg-gray-900 text-gray-100 text-sm rounded-lg shadow-lg">
    <div x-data="{ activeTab: 'kontrakan' }" class="w-full">
        <!-- Tab Navigation -->
        <div class="flex space-x-2 border-b border-gray-300">
            <button @click="activeTab = 'kontrakan'"
                :class="activeTab === 'kontrakan'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Kontrakan
            </button>

            <button @click="activeTab = 'telur'"
                :class="activeTab === 'telur'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Telur
            </button>

            <button @click="activeTab = 'gas'"
                :class="activeTab === 'gas'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Gas
            </button>
        </div>

        <!-- Tab Content -->
        <div class="mt-4 p-4 bg-black rounded-lg shadow border">
            @livewire('finance.master-kontrakan') {{-- Include the Setting Sub Bagian Livewire component --}}
            @livewire('finance.rekening-telur', key('rekening-telur')) {{-- Include the Setting Sub Bagian Livewire component --}}
            @livewire('finance.rekening-gas', key('rekening-gas')) {{-- Include the Setting Sub Bagian Livewire component --}}
        </div>
    </div>
</div>
