<div>
    <?php
        $fmtRp  = fn($v) => number_format((int)$v, 0, ',', '.');
        $fmtPct = fn($v) => ($v === null || $v === '-') ? '-' : rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') . '%';

        $pct = function ($num, $den) use ($fmtPct) {
            $den = (float)$den;
            if ($den == 0.0) return '0%';
            return $fmtPct(((float)$num / $den) * 100);
        };

        $pctSelisih = function ($selisihRp, $hrg) use ($fmtPct) {
            $hrg = (float)$hrg;
            $sel = (float)$selisihRp;
            $baseline = $hrg - $sel;
            if ($baseline == 0.0) return '0%';
            return $fmtPct(($sel / $baseline) * 100);
        };

        $color = function ($v) {
            $val = (float)$v;
            if ($val < 0) return 'text-rose-600 font-medium';
            if ($val > 0) return 'text-emerald-600 font-medium';
            return 'text-gray-400';
        };

        /**
         * ==================================================
         * Aggregate data by wilayah (sum across all dates)
         * ==================================================
         */
        $summaryByWilayah = [];
        foreach (($rows ?? []) as $tgl => $byWilayah) {
            foreach (($byWilayah ?? []) as $wilayahName => $list) {
                if (!isset($summaryByWilayah[$wilayahName])) {
                    $summaryByWilayah[$wilayahName] = [
                        'BY BULAN LALU' => [
                            'hrg' => 0,
                            'selisih_rp' => 0,
                            'kontribusi' => 0,
                            'disc_rp' => 0,
                            'retur_rp' => 0,
                            'gas_rp' => 0,
                            'telur_rp' => 0,
                            'loss_bahan' => 0,
                            'kurang_setoran' => 0,
                            'total_kontribusi' => 0,
                        ],
                        'BY TARGET' => [
                            'hrg' => 0,
                            'selisih_rp' => 0,
                            'kontribusi' => 0,
                            'disc_rp' => 0,
                            'retur_rp' => 0,
                            'gas_rp' => 0,
                            'telur_rp' => 0,
                            'loss_bahan' => 0,
                            'kurang_setoran' => 0,
                            'total_kontribusi' => 0,
                        ],
                    ];
                }
                
                foreach ($list as $r) {
                    $type = $r['type'] ?? 'BY BULAN LALU';
                    $summaryByWilayah[$wilayahName][$type]['hrg'] += (int)($r['hrg'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['selisih_rp'] += (int)($r['selisih_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['kontribusi'] += (int)($r['kontribusi'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['disc_rp'] += (int)($r['disc_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['retur_rp'] += (int)($r['retur_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['gas_rp'] += (int)($r['gas_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['telur_rp'] += (int)($r['telur_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['loss_bahan'] += (int)($r['loss_bahan'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['kurang_setoran'] += (int)($r['kurang_setoran'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['total_kontribusi'] += (int)($r['total_kontribusi'] ?? 0);
                }
            }
        }
        ksort($summaryByWilayah);
    ?>

    <div class="space-y-4">

        
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold flex items-center gap-2">
                        RINGKASAN KONTRIBUSI PER WILAYAH
                        <!--[if BLOCK]><![endif]--><?php if($loadDuration): ?>
                            <span class="text-[10px] text-gray-400 font-normal border border-gray-200 bg-gray-50 px-2 py-0.5 rounded-full">
                                <?php echo e($loadDuration); ?>s
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="text-xs text-gray-500">Total kontribusi per wilayah dalam periode <?php echo e(\Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y')); ?>.</div>
                </div>

                <div class="flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
                        <input type="date" wire:model="tanggalAwal"
                               class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
                        <input type="date" wire:model="tanggalAkhir"
                               class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    
                    <div class="w-full sm:w-auto pb-0.5 flex items-center gap-2">
                        <button wire:click="load"
                                wire:loading.attr="disabled"
                                wire:target="load"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="load">Tampilkan</span>
                            <span wire:loading wire:target="load">Loading...</span>
                        </button>

                        <button wire:click="download"
                                wire:loading.attr="disabled"
                                wire:target="download"
                                class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="download">📥 Download Excel</span>
                            <span wire:loading wire:target="download">Menyiapkan...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                <div class="mt-3 text-xs text-red-600"><?php echo e(session('message')); ?></div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
            <table class="min-w-[1400px] w-full text-xs text-left">
                <thead class="text-[11px] uppercase text-gray-700 bg-gray-50 border-b-2 border-gray-200 font-semibold">
                    <tr>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100 w-32">WILAYAH</th>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100 w-28">JENIS</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Selisih %</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">(+/-) Rp</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Kontribusi</th>

                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">DISC MANUAL</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">RETUR</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">GAS</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">TELUR</th>

                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle bg-gray-100">LOSS BAHAN</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle bg-red-50 border-red-200 text-red-700">KURANG SETORAN</th>
                        <th rowspan="2" class="px-3 py-2 text-right align-middle bg-gray-50">TOTAL KONTRIBUSI</th>
                    </tr>
                    <tr>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $summaryByWilayah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wilayahName => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <?php
                        $rBL = $data['BY BULAN LALU'] ?? [];
                        $rTG = $data['BY TARGET'] ?? [];
                    ?>

                    
                    <tr class="hover:bg-gray-50">
                        <td rowspan="2" class="px-3 py-2 border-r align-top font-bold bg-white text-gray-900 whitespace-nowrap">
                            <?php echo e($wilayahName); ?>

                        </td>

                        <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY BULAN LALU</td>

                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['selisih_rp'] ?? 0)); ?>"><?php echo e($pctSelisih($rBL['selisih_rp'] ?? 0, $rBL['hrg'] ?? 0)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['selisih_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['selisih_rp'] ?? 0)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['kontribusi'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['hrg'] > 0 ? round(($rBL['disc_rp'] / $rBL['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['disc_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['disc_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['hrg'] > 0 ? round(($rBL['retur_rp'] / $rBL['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['retur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['retur_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['hrg'] > 0 ? round(($rBL['gas_rp'] / $rBL['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['gas_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['gas_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['hrg'] > 0 ? round(($rBL['telur_rp'] / $rBL['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['telur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['telur_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['loss_bahan'] ?? 0)); ?>">
                            <button type="button" class="underline decoration-dashed underline-offset-4" wire:click="openLossModal('<?php echo e(addslashes($wilayahName)); ?>')">
                                <?php echo e($fmtRp($rBL['loss_bahan'] ?? 0)); ?>

                            </button>
                        </td>
                        <td class="px-3 py-2 text-right border-r text-red-600 font-medium">
                            <?php $ks = (int)($rBL['kurang_setoran'] ?? 0); ?>
                            <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                        </td>
                        <td class="px-3 py-2 text-right font-bold <?php echo e($color($rBL['total_kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['total_kontribusi'] ?? 0)); ?></td>
                    </tr>

                    
                    <tr class="hover:bg-gray-50 border-b-2 border-gray-300">
                        <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY TARGET</td>

                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['selisih_rp'] ?? 0)); ?>"><?php echo e($pctSelisih($rTG['selisih_rp'] ?? 0, $rTG['hrg'] ?? 0)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['selisih_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['selisih_rp'] ?? 0)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['kontribusi'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['hrg'] > 0 ? round(($rTG['disc_rp'] / $rTG['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['disc_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['disc_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['hrg'] > 0 ? round(($rTG['retur_rp'] / $rTG['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['retur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['retur_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['hrg'] > 0 ? round(($rTG['gas_rp'] / $rTG['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['gas_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['gas_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['hrg'] > 0 ? round(($rTG['telur_rp'] / $rTG['hrg']) * 100, 2) : null)); ?></td>
                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['telur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['telur_rp'] ?? 0)); ?></td>

                        <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['loss_bahan'] ?? 0)); ?>">
                            <button type="button" class="underline decoration-dashed underline-offset-4" wire:click="openLossModal('<?php echo e(addslashes($wilayahName)); ?>')">
                                <?php echo e($fmtRp($rTG['loss_bahan'] ?? 0)); ?>

                            </button>
                        </td>
                        <td class="px-3 py-2 text-right border-r text-red-600 font-medium">
                            <?php $ks = (int)($rTG['kurang_setoran'] ?? 0); ?>
                            <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                        </td>
                        <td class="px-3 py-2 text-right font-bold <?php echo e($color($rTG['total_kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['total_kontribusi'] ?? 0)); ?></td>
                    </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="15" class="px-6 py-6 text-center text-gray-500">
                            Silakan pilih periode dan klik Tampilkan.
                        </td>
                    </tr>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>

                
                <!--[if BLOCK]><![endif]--><?php if(!empty($grandTotals)): ?>
                    <tfoot class="text-xs font-bold bg-gray-100 border-t-2 border-gray-300">
                        <tr class="border-b">
                            <td colspan="2" class="px-3 py-2 text-center border-r bg-gray-200">TOTAL ALL (BY TARGET)</td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['selisih_rp'])); ?>">
                                <?php echo e($pctSelisih($grandTotals['target']['selisih_rp'], $grandTotals['target']['hrg'])); ?>

                            </td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['selisih_rp'])); ?>"><?php echo e($fmtRp($grandTotals['target']['selisih_rp'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['kontribusi'])); ?>"><?php echo e($fmtRp($grandTotals['target']['kontribusi'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['target']['disc'], $grandTotals['target']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['disc'])); ?>"><?php echo e($fmtRp($grandTotals['target']['disc'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['target']['retur'], $grandTotals['target']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['retur'])); ?>"><?php echo e($fmtRp($grandTotals['target']['retur'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['target']['gas'], $grandTotals['target']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['gas'])); ?>"><?php echo e($fmtRp($grandTotals['target']['gas'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['target']['telur'], $grandTotals['target']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['telur'])); ?>"><?php echo e($fmtRp($grandTotals['target']['telur'])); ?></td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['target']['loss_bahan'])); ?>"><?php echo e($fmtRp($grandTotals['target']['loss_bahan'])); ?></td>
                            <td class="px-3 py-2 text-right border-r text-red-600 font-bold">
                                <?php $ks = (int)($grandTotals['target']['kurang_setoran'] ?? 0); ?>
                                <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                            </td>
                            <td class="px-3 py-2 text-right bg-yellow-50 <?php echo e($color($grandTotals['target']['total_kontribusi'])); ?>"><?php echo e($fmtRp($grandTotals['target']['total_kontribusi'])); ?></td>
                        </tr>

                        <tr>
                            <td colspan="2" class="px-3 py-2 text-center border-r bg-gray-200">TOTAL ALL (BY BL)</td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['selisih_rp'])); ?>">
                                <?php echo e($pctSelisih($grandTotals['bl']['selisih_rp'], $grandTotals['bl']['hrg'])); ?>

                            </td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['selisih_rp'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['selisih_rp'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['kontribusi'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['kontribusi'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['bl']['disc'], $grandTotals['bl']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['disc'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['disc'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['bl']['retur'], $grandTotals['bl']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['retur'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['retur'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['bl']['gas'], $grandTotals['bl']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['gas'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['gas'])); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-600"><?php echo e($pct($grandTotals['bl']['telur'], $grandTotals['bl']['hrg'])); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['telur'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['telur'])); ?></td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($grandTotals['bl']['loss_bahan'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['loss_bahan'])); ?></td>
                            <td class="px-3 py-2 text-right border-r text-red-600 font-bold">
                                <?php $ks = (int)($grandTotals['bl']['kurang_setoran'] ?? 0); ?>
                                <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                            </td>
                            <td class="px-3 py-2 text-right bg-yellow-50 <?php echo e($color($grandTotals['bl']['total_kontribusi'])); ?>"><?php echo e($fmtRp($grandTotals['bl']['total_kontribusi'])); ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            </table>
        </div>

        
        <!--[if BLOCK]><![endif]--><?php if($showLossModal): ?>
            <div class="fixed inset-0 z-40 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/40" wire:click="closeLossModal"></div>

                <div class="relative z-50 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Detail Loss Bahan</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Wilayah: <span class="font-semibold text-gray-800"><?php echo e($lossModalWilayah); ?></span></div>
                        </div>
                        <button wire:click="closeLossModal" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
                    </div>

                    <div class="mt-4 max-h-72 overflow-y-auto text-sm space-y-3">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $lossModalItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toko => $barangs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                <div class="text-gray-900 font-semibold text-[13px] mb-1"><?php echo e($toko); ?></div>
                                <ul class="list-disc list-inside text-gray-700 text-[12px] space-y-1">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $barangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="flex items-center justify-between gap-3">
                                            <span class="truncate"><?php echo e($barang['barang'] ?? '-'); ?></span>
                                            <span class="text-gray-900 font-semibold"><?php echo e(number_format((int)($barang['nominal'] ?? 0), 0, ',', '.')); ?></span>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </ul>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="py-6 text-center text-gray-500 text-sm">Tidak ada data loss untuk wilayah ini.</div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button wire:click="closeLossModal" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-700 text-xs hover:bg-gray-50">Tutup</button>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>
<?php /**PATH C:\laragon\www\hopusatrunningserverv3\resources\views/livewire/operasional/kontribusi-harian-wilayah.blade.php ENDPATH**/ ?>