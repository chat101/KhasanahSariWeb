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
         * TRANSPOSE rows: dari [outlet][tgl][] => [tgl][outlet][]
         * (supaya tampilan bisa group by tanggal seperti gambar)
         * ==================================================
         */
        $rowsByTanggal = [];
        foreach (($rows ?? []) as $outletName => $byTanggal) {
            foreach (($byTanggal ?? []) as $tgl => $list) {
                $rowsByTanggal[$tgl] ??= [];
                $rowsByTanggal[$tgl][$outletName] = $list;
            }
        }
        ksort($rowsByTanggal);

        // fungsi ambil row per jenis
        $pickType = function(array $list, string $type) {
            foreach ($list as $r) {
                if (strtoupper(trim((string)($r['type'] ?? ''))) === $type) return $r;
            }
            return null;
        };
    ?>

    <div class="space-y-4">

        
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold flex items-center gap-2">
                        DETAIL KONTRIBUSI HARIAN AREA
                        <!--[if BLOCK]><![endif]--><?php if($loadDuration): ?>
                            <span class="text-[10px] text-gray-400 font-normal border border-gray-200 bg-gray-50 px-2 py-0.5 rounded-full">
                                <?php echo e($loadDuration); ?>s
                            </span>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="text-xs text-gray-500">Menampilkan detail harian seluruh toko di area ini.</div>
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

                        <button @click="document.getElementById('downloadForm').submit()"
                                class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                            <span>📥 Download Excel</span>
                            <span wire:loading wire:target="download">Menyiapkan...</span>
                        </button>
                    </div>
                </div>

                
                <!--[if BLOCK]><![endif]--><?php if($showLossModal): ?>
                    <div class="fixed inset-0 z-40 flex items-center justify-center px-4">
                        <div class="absolute inset-0 bg-black/40" wire:click="closeLossModal"></div>

                        <div class="relative z-50 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Detail Loss Bahan</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Outlet: <span class="font-semibold text-gray-800"><?php echo e($lossModalOutlet); ?></span></div>
                                    <!--[if BLOCK]><![endif]--><?php if($lossModalTanggal): ?>
                                        <div class="text-[11px] text-gray-500">Tanggal: <span class="font-semibold text-gray-800"><?php echo e(\Carbon\Carbon::parse($lossModalTanggal)->format('d/m/Y')); ?></span></div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <button wire:click="closeLossModal" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
                            </div>

                            <div class="mt-4 max-h-72 overflow-y-auto text-sm space-y-3">
                                <!--[if BLOCK]><![endif]--><?php if(!empty($lossModalItems)): ?>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lossModalItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 flex items-center justify-between gap-3">
                                            <div>
                                                <div class="text-gray-800 text-[12px] leading-snug"><?php echo e($barang['barang'] ?? '-'); ?></div>
                                                <!--[if BLOCK]><![endif]--><?php if(!empty($barang['satuan'])): ?>
                                                    <div class="text-[11px] text-gray-500">Satuan: <?php echo e($barang['satuan']); ?></div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                            <div class="text-right">
                                                <!--[if BLOCK]><![endif]--><?php if(isset($barang['qty']) && (int)$barang['qty'] > 0): ?>
                                                    <div class="text-gray-900 font-semibold"><?php echo e(number_format((int)$barang['qty'], 0, ',', '.')); ?></div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif((int)($lossModalTotal ?? 0) > 0): ?>
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 text-center">
                                        <div class="text-[11px] text-gray-500 mb-1">Total Loss</div>
                                        <div class="text-lg font-semibold text-gray-900"><?php echo e(number_format((int)$lossModalTotal, 0, ',', '.')); ?></div>
                                        <div class="text-[11px] text-gray-500 mt-1">(tidak ada rincian barang)</div>
                                    </div>
                                <?php else: ?>
                                    <div class="py-6 text-center text-gray-500 text-sm">Tidak ada data loss pada tanggal ini.</div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button wire:click="closeLossModal" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-700 text-xs hover:bg-gray-50">Tutup</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
                <div class="mt-3 text-xs text-red-600"><?php echo e(session('message')); ?></div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
            <table class="min-w-[1600px] w-full text-xs text-left">
                <thead class="text-[11px] uppercase text-gray-700 bg-gray-50 border-b-2 border-gray-200 font-semibold">
                    <tr>
                        
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">TANGGAL</th>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">OUTLET</th>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">JENIS</th>

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
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $rowsByTanggal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tgl => $byOutlet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <?php
                        // rowspan tanggal = total outlet * 2 baris (BL+TARGET)
                        $rowspanTanggal = collect($byOutlet)->count() * 2;
                        $printedTanggal = false;
                    ?>

                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $byOutlet; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $outletName => $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $outletKey = strtoupper(trim($outletName));
                            $rBL = $pickType($list, 'BY BULAN LALU');
                            $rTG = $pickType($list, 'BY TARGET');
                            $tglDate = \Carbon\Carbon::parse($tgl)->toDateString();
                            $hasLossDetail = !empty($lossBarangListMap[$outletKey][$tglDate] ?? []);
                            $hasNominalBL = (int)($rBL['loss_bahan'] ?? 0) > 0;
                            $hasNominalTG = (int)($rTG['loss_bahan'] ?? 0) > 0;
                            $clickableBL = $hasLossDetail || $hasNominalBL;
                            $clickableTG = $hasLossDetail || $hasNominalTG;
                        ?>

                        
                        <tr class="hover:bg-gray-50">
                            <!--[if BLOCK]><![endif]--><?php if(!$printedTanggal): ?>
                                <td rowspan="<?php echo e($rowspanTanggal); ?>"
                                    class="px-3 py-2 border-r text-center align-top font-semibold bg-white text-gray-900 whitespace-nowrap">
                                    <?php echo e(\Carbon\Carbon::parse($tgl)->format('d/m/Y')); ?>

                                </td>
                                <?php $printedTanggal = true; ?>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <td rowspan="2" class="px-3 py-2 border-r align-top font-bold bg-white text-gray-900 whitespace-nowrap">
                                <?php echo e($outletName); ?>

                            </td>

                            <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY BULAN LALU</td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['selisih_persen'] ?? 0)); ?>"><?php echo e($fmtPct($rBL['selisih_persen'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['selisih_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['selisih_rp'] ?? 0)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['kontribusi'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['disc_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['disc_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['disc_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['retur_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['retur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['retur_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['gas_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['gas_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['gas_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rBL['telur_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['telur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['telur_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rBL['loss_bahan'] ?? 0)); ?>">
                                <!--[if BLOCK]><![endif]--><?php if($clickableBL): ?>
                                    <button type="button" class="underline decoration-dashed underline-offset-4" wire:click="openLossModal('<?php echo e(addslashes($outletKey)); ?>', '<?php echo e($tgl); ?>', <?php echo e((int)($rBL['loss_bahan'] ?? 0)); ?>, '<?php echo e(addslashes($outletName)); ?>')">
                                        <?php echo e($fmtRp($rBL['loss_bahan'] ?? 0)); ?>

                                    </button>
                                <?php else: ?>
                                    <?php echo e($fmtRp($rBL['loss_bahan'] ?? 0)); ?>

                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="px-3 py-2 text-right border-r text-red-600 font-medium">
                                <?php $ks = (int)($rBL['kurang_setoran'] ?? 0); ?>
                                <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                            </td>
                            <td class="px-3 py-2 text-right font-bold <?php echo e($color($rBL['total_kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rBL['total_kontribusi'] ?? 0)); ?></td>
                        </tr>

                        
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY TARGET</td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['selisih_persen'] ?? 0)); ?>"><?php echo e($fmtPct($rTG['selisih_persen'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['selisih_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['selisih_rp'] ?? 0)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['kontribusi'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['disc_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['disc_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['disc_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['retur_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['retur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['retur_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['gas_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['gas_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['gas_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r text-gray-500"><?php echo e($fmtPct($rTG['telur_pct'] ?? null)); ?></td>
                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['telur_rp'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['telur_rp'] ?? 0)); ?></td>

                            <td class="px-3 py-2 text-right border-r <?php echo e($color($rTG['loss_bahan'] ?? 0)); ?>">
                                <!--[if BLOCK]><![endif]--><?php if($clickableTG): ?>
                                    <button type="button" class="underline decoration-dashed underline-offset-4" wire:click="openLossModal('<?php echo e(addslashes($outletKey)); ?>', '<?php echo e($tgl); ?>', <?php echo e((int)($rTG['loss_bahan'] ?? 0)); ?>, '<?php echo e(addslashes($outletName)); ?>')">
                                        <?php echo e($fmtRp($rTG['loss_bahan'] ?? 0)); ?>

                                    </button>
                                <?php else: ?>
                                    <?php echo e($fmtRp($rTG['loss_bahan'] ?? 0)); ?>

                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="px-3 py-2 text-right border-r text-red-600 font-medium">
                                <?php $ks = (int)($rTG['kurang_setoran'] ?? 0); ?>
                                <?php echo e($ks === 0 ? '-' : ('-' . number_format($ks, 0, ',', '.'))); ?>

                            </td>
                            <td class="px-3 py-2 text-right font-bold <?php echo e($color($rTG['total_kontribusi'] ?? 0)); ?>"><?php echo e($fmtRp($rTG['total_kontribusi'] ?? 0)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="16" class="px-6 py-6 text-center text-gray-500">
                            Silakan pilih periode dan klik Tampilkan.
                        </td>
                    </tr>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>

                
                <!--[if BLOCK]><![endif]--><?php if(!empty($grandTotals)): ?>
                    <tfoot class="text-xs font-bold bg-gray-100 border-t-2 border-gray-300">
                        <tr class="border-b">
                            <td colspan="3" class="px-3 py-2 text-center border-r bg-gray-200">GRAND TOTAL ALL (BY TARGET)</td>

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
                            <td colspan="3" class="px-3 py-2 text-center border-r bg-gray-200">GRAND TOTAL ALL (BY BL)</td>

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
    </div>

    
    <form id="downloadForm" method="POST" action="<?php echo e(route('kontribusi-area.download')); ?>" style="display:none;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tanggalAwal" value="<?php echo e($tanggalAwal); ?>">
        <input type="hidden" name="tanggalAkhir" value="<?php echo e($tanggalAkhir); ?>">
        <input type="hidden" name="tokosUser" value="<?php echo e(json_encode($tokosUser)); ?>">
    </form>
</div>
<?php /**PATH C:\laragon\www\hopusatrunningserverv3\resources\views/livewire/operasional/kontribusi-harian-area.blade.php ENDPATH**/ ?>