<div class="space-y-4" wire:key="laporan-kontribusi-root">

    
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                    LK
                </span>
                Laporan Kontribusi
            </h2>
            <span class="text-[11px] text-gray-500">Monitoring kontribusi</span>
        </div>

        
        <div class="flex flex-wrap items-center gap-2 text-[11px]">
            <button type="button"
                    wire:click="setTab('target')"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'target',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'target',
                    ]); ?>">
                Report by Target
            </button>

            <button type="button"
                    wire:click="setTab('bulanlalu')"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'bulanlalu',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'bulanlalu',
                    ]); ?>">
                By Bulan Lalu
            </button>

            <button type="button"
                    wire:click="setTab('daily')"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'daily',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'daily',
                    ]); ?>">
                Detail Harian Area
            </button>

            <button type="button"
                    wire:click="setTab('dailywilayah')"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'dailywilayah',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'dailywilayah',
                    ]); ?>">
                Detail Harian Wilayah
            </button>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow border border-gray-200 p-3">
        <!--[if BLOCK]><![endif]--><?php if($tab === 'target'): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('operasional.kontribusi-target', ['tokosUser' => $tokosUser]);

$__html = app('livewire')->mount($__name, $__params, 'kontribusi-target', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php elseif($tab === 'bulanlalu'): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('operasional.kontribusi-bulan-lalu', ['tokosUser' => $tokosUser]);

$__html = app('livewire')->mount($__name, $__params, 'kontribusi-bulanlalu', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php elseif($tab === 'dailywilayah'): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('operasional.kontribusi-harian-wilayah', ['tokosUser' => $tokosUser]);

$__html = app('livewire')->mount($__name, $__params, 'kontribusi-dailywilayah', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php else: ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('operasional.kontribusi-harian-area', ['tokosUser' => $tokosUser]);

$__html = app('livewire')->mount($__name, $__params, 'kontribusi-daily', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

</div>
<?php /**PATH C:\laragon\www\hopusatrunningserverv3\resources\views/livewire/operasional/laporan-kontribusi.blade.php ENDPATH**/ ?>