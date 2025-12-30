<?php
    $computedTitle = ($pageTitle ?? $title ?? (request()->route()?->getName() ? \Illuminate\Support\Str::headline(str_replace('.', ' ', request()->route()->getName())) : 'Dashboard'));
    $computedSubtitle = $pageSubtitle ?? null;
    $user = auth()->user();
    $userInitials = $user?->initials() ?? '';
    $userArea = $user?->area?->nama_area ?? 'PUSAT';
?>

<div class="hidden lg:block px-4 pt-4">
    <div
        class="relative flex items-center justify-between gap-3 rounded-2xl border border-white/5 bg-gradient-to-r from-slate-900/80 via-slate-900/70 to-slate-900/80 text-slate-100 shadow-2xl backdrop-blur-xl px-5 py-4">
        <div class="flex items-center gap-3">
            <?php if (isset($component)) { $__componentOriginal1b6467b07b302021134396bbd98e74a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b6467b07b302021134396bbd98e74a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.toggle','data' => ['icon' => 'bars-2','class' => 'hidden lg:flex items-center justify-center w-10 h-10 rounded-xl border border-white/10 bg-white/5 text-slate-200 hover:bg-white/10 transition']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bars-2','class' => 'hidden lg:flex items-center justify-center w-10 h-10 rounded-xl border border-white/10 bg-white/5 text-slate-200 hover:bg-white/10 transition']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b6467b07b302021134396bbd98e74a9)): ?>
<?php $attributes = $__attributesOriginal1b6467b07b302021134396bbd98e74a9; ?>
<?php unset($__attributesOriginal1b6467b07b302021134396bbd98e74a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b6467b07b302021134396bbd98e74a9)): ?>
<?php $component = $__componentOriginal1b6467b07b302021134396bbd98e74a9; ?>
<?php unset($__componentOriginal1b6467b07b302021134396bbd98e74a9); ?>
<?php endif; ?>

            <div class="space-y-0.5">
                <h1 class="text-xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-200 to-blue-200">
                    <?php echo e($computedTitle); ?>

                </h1>
                <?php if($computedSubtitle): ?>
                    <p class="text-xs text-slate-400 font-medium">
                        <?php echo e($computedSubtitle); ?>

                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-full bg-white/5 border border-white/10 px-3 py-1.5">
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 p-[1px]">
                <div class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center text-white font-bold text-[10px] uppercase">
                    <?php echo e($userInitials); ?>

                </div>
            </div>
            <div class="flex flex-col text-right leading-tight">
                <span class="text-xs font-bold text-white"><?php echo e($user?->name); ?></span>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest"><?php echo e(strtoupper($userArea)); ?></span>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\hopusatrunningserverv3\resources\views/components/layouts/app/topbar.blade.php ENDPATH**/ ?>