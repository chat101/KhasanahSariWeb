<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">

<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


</head>

<body class="min-h-screen bg-slate-950 text-slate-100 font-sans dark:bg-slate-950">
    <?php if (isset($component)) { $__componentOriginal17e56bc23bb0192e474b351c4358d446 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal17e56bc23bb0192e474b351c4358d446 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.index','data' => ['sticky' => true,'stashable' => true,'class' => 'border-r border-zinc-200 bg-gradient-to-b from-zinc-50 to-white dark:border-zinc-700 dark:from-zinc-900 dark:to-zinc-950 shadow-2xl text-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['sticky' => true,'stashable' => true,'class' => 'border-r border-zinc-200 bg-gradient-to-b from-zinc-50 to-white dark:border-zinc-700 dark:from-zinc-900 dark:to-zinc-950 shadow-2xl text-sm']); ?>
        
        <!-- Logo/Brand Section -->
        <div class="px-4 py-5 mb-2 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-900 dark:via-purple-900 dark:to-pink-900 shadow-lg">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-base font-bold tracking-tight leading-tight">Khasanah Sari</h1>
                    <p class="text-xs text-white/80 mt-0.5">ERP System</p>
                </div>
            </div>
        </div>
        
        <?php if (isset($component)) { $__componentOriginal1b6467b07b302021134396bbd98e74a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b6467b07b302021134396bbd98e74a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.toggle','data' => ['class' => 'lg:hidden mb-4','icon' => 'x-mark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:hidden mb-4','icon' => 'x-mark']); ?>
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
        
        <?php if (isset($component)) { $__componentOriginalacac6a48a34186ea0abd369a00e5e2d4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalacac6a48a34186ea0abd369a00e5e2d4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.index','data' => ['variant' => 'outline','class' => 'px-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','class' => 'px-3']); ?>
            <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['class' => 'grid gap-1 mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'grid gap-1 mb-4']); ?>
                <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('dashboard'),'current' => request()->routeIs('dashboard'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-md transition-all duration-300 rounded-lg font-semibold']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('dashboard')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-md transition-all duration-300 rounded-lg font-semibold']); ?>
                    <?php echo e(__('Dashboard')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'MasterData')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'heading' => '📊 MASTER DATA','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'heading' => '📊 MASTER DATA','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'muser')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'user-circle','href' => route('muser'),'current' => request()->routeIs('muser'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'user-circle','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('muser')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('muser')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master User')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'mtoko')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'building-storefront','href' => route('mtoko'),'current' => request()->routeIs('mtoko'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'building-storefront','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('mtoko')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('mtoko')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Toko')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'master.wilayah')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'map','href' => route('master.wilayah'),'current' => request()->routeIs('master.wilayah'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'map','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('master.wilayah')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('master.wilayah')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Wilayah')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'master.area')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'map-pin','href' => route('master.area'),'current' => request()->routeIs('master.area'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'map-pin','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('master.area')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('master.area')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Area')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'msupplier')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'truck','href' => route('msupplier'),'current' => request()->routeIs('msupplier'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'truck','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('msupplier')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('msupplier')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Supplier')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'mbarang')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'cube','href' => route('mbarang'),'current' => request()->routeIs('mbarang'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'cube','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('mbarang')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('mbarang')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Barang')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'mproduk')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'shopping-bag','href' => route('mproduk'),'current' => request()->routeIs('mproduk'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'shopping-bag','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('mproduk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('mproduk')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Produk')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'slides.manage')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'photo','href' => route('slides.manage'),'current' => request()->routeIs('slides.manage'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'photo','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('slides.manage')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('slides.manage')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Master Slide')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'mesintoproduk')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'cog','href' => route('mesintoproduk'),'current' => request()->routeIs('mesintoproduk'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'cog','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('mesintoproduk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('mesintoproduk')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Setting Produk Ke Mesin')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'master-target-kontribusi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'chart-bar','href' => route('master-target-kontribusi'),'current' => request()->routeIs('master-target-kontribusi'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'chart-bar','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('master-target-kontribusi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('master-target-kontribusi')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Target Kontribusi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'upload-proyeksi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'arrow-up-tray','href' => route('upload-proyeksi'),'current' => request()->routeIs('master-target-kontribusi'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'arrow-up-tray','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('upload-proyeksi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('master-target-kontribusi')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('UPLOAD PROYEKSI')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'masterdata', 'master.trend-inflasi')): ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'chart-bar','href' => route('master.trend-inflasi'),'current' => request()->routeIs('master.trend-inflasi'),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'chart-bar','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('master.trend-inflasi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('master.trend-inflasi')),'wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg']); ?>
                        <?php echo e(__('Master Trend Inflasi')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'Gudang')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '📦 GUDANG','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '📦 GUDANG','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'inbox','href' => route('brgmsk'),'current' => request()->routeIs('brgmsk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'inbox','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('brgmsk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('brgmsk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Input Barang Masuk')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('rekapbrgmsk'),'current' => request()->routeIs('rekapbrgmsk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('rekapbrgmsk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('rekapbrgmsk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Rekap Hasil Input')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'Operasional')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '⚙️ OPERASIONAL','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '⚙️ OPERASIONAL','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'operasional', 'loss-bahan')): ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('loss-bahan'),'current' => request()->routeIs('loss-bahan'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('loss-bahan')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('loss-bahan')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                    <?php echo e(__('Input Loss Bahan')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                <?php endif; ?>
                <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'operasional', 'sisa-sales')): ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('sisa-sales'),'current' => request()->routeIs('sisa-sales'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('sisa-sales')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('sisa-sales')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Laporan Kontribusi')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                <?php endif; ?>
                <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'operasional', 'kontribusi-harian-toko')): ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('kontribusi-harian-toko'),'current' => request()->routeIs('kontribusi-harian-toko'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('kontribusi-harian-toko')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('kontribusi-harian-toko')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                    <?php echo e(__('KONTRIBUSI HARIAN TOKO')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                <?php endif; ?>
                <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'operasional', 'kurang-setoran')): ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('kurang-setoran'),'current' => request()->routeIs('kurang-setoran'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('kurang-setoran')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('kurang-setoran')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                    <?php echo e(__('Kurang Setoran')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'Purchasing')): ?>
                
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '🛒 PURCHASING','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '🛒 PURCHASING','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('listsuppmasuk'),'current' => request()->routeIs('listsuppmasuk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('listsuppmasuk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('listsuppmasuk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Supplier Masuk')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('rekapinputsuppmasuk'),'current' => request()->routeIs('rekapinputsuppmasuk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('rekapinputsuppmasuk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('rekapinputsuppmasuk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Rekap Input Masuk')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>

            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'ACCOUNTING')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '💰 ACCOUNTING','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '💰 ACCOUNTING','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'bank.index')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('bank.index'),'current' => request()->routeIs('bank.index'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('bank.index')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('bank.index')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER BANK')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.master-jenis-transaksi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.master-jenis-transaksi'),'current' => request()->routeIs('keuangan.master-jenis-transaksi'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.master-jenis-transaksi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.master-jenis-transaksi')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER JENIS TRANSAKSI')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.master-akun-biaya')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.master-akun-biaya'),'current' => request()->routeIs('keuangan.master-akun-biaya'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.master-akun-biaya')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.master-akun-biaya')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER AKUN BIAYA')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.master-kas')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.master-kas'),'current' => request()->routeIs('keuangan.master-kas'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.master-kas')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.master-kas')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER KAS')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.master-template-jurnal')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.master-template-jurnal'),'current' => request()->routeIs('keuangan.master-template-jurnal'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.master-template-jurnal')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.master-template-jurnal')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER TEMPLATE JURNAL')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.master-role-coa')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.master-role-coa'),'current' => request()->routeIs('keuangan.master-role-coa'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.master-role-coa')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.master-role-coa')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MASTER ROLE COA')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'transaksi.index')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('transaksi.index'),'current' => request()->routeIs('transaksi.index'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('transaksi.index')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('transaksi.index')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('BUKU BANK')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'monitor-biaya')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('monitor-biaya'),'current' => request()->routeIs('monitor-biaya'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('monitor-biaya')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('monitor-biaya')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('MONITOR BIAYA')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'ACCOUNTING', 'keuangan.jurnal.input')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('keuangan.jurnal.input'),'current' => request()->routeIs('keuangan.jurnal.input'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('keuangan.jurnal.input')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('keuangan.jurnal.input')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Jurnal Input')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>


            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'FINANCE')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '💵 FINANCE','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '💵 FINANCE','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'FINANCE', 'masterrekening')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('masterrekening'),'current' => request()->routeIs('masterrekening'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('masterrekening')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('masterrekening')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Master Rekening')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'FINANCE', 'uangmsk')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('uangmsk'),'current' => request()->routeIs('uangmsk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('uangmsk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('uangmsk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Uang Setoran Toko')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'FINANCE', 'uangmskperiode')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('uangmskperiode'),'current' => request()->routeIs('uangmskperiode'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('uangmskperiode')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('uangmskperiode')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Uang Masuk PerPeriode')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'FINANCE', 'biayainputpusat')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('biayainputpusat'),'current' => request()->routeIs('biayainputpusat'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('biayainputpusat')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('biayainputpusat')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Piutang Supplier')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'FINANCE', 'hutang.dagang.index')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('hutang.dagang.index'),'current' => request()->routeIs('hutang.dagang.index'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hutang.dagang.index')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hutang.dagang.index')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('HUTANG DAGANG')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'PRODUKSI')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '🏭 PRODUKSI','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '🏭 PRODUKSI','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'perproduksi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('perproduksi'),'current' => request()->routeIs('perproduksi'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('perproduksi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('perproduksi')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Perintah Produksi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'listperproduksi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('listperproduksi'),'current' => request()->routeIs('listperproduksi'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('listperproduksi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('listperproduksi')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Hasil Produksi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'setjob')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('setjob'),'current' => request()->routeIs('setjob'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('setjob')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('setjob')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Setting Bagian')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'produktifitas')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('produktifitas'),'current' => request()->routeIs('produktifitas'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('produktifitas')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('produktifitas')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Produktifitas')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'selesaikanjob')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('selesaikanjob'),'current' => request()->routeIs('selesaikanjob'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('selesaikanjob')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('selesaikanjob')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Selesai Divisi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'work-order')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('work-order'),'current' => request()->routeIs('work-order'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('work-order')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('work-order')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Work Order')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'hsldivisi')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('hsldivisi'),'current' => request()->routeIs('hsldivisi'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hsldivisi')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('hsldivisi')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Hasil PerDivisi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'opnamepenyesuaian')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('opnamepenyesuaian'),'current' => request()->routeIs('opnamepenyesuaian'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opnamepenyesuaian')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('opnamepenyesuaian')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Penyesuaian Stok')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'PRODUKSI', 'komplen')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('komplen'),'current' => request()->routeIs('komplen'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('komplen')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('komplen')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Complain')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>

            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'TEKNISI')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '🔧 TEKNISI','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '🔧 TEKNISI','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'TEKNISI', 'ticket.web.create')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('ticket.web.create'),'current' => request()->routeIs('ticket.web.create'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ticket.web.create')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('ticket.web.create')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Tiket Teknisi')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
            
            <?php if (\Illuminate\Support\Facades\Blade::check('menugroup', 'LAPORAN')): ?>
                <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['expandable' => true,'expanded' => request()->routeIs('dashboard') || request()->routeIs('dashboard'),'heading' => '📋 LAPORAN','class' => 'grid gap-1 mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard') || request()->routeIs('dashboard')),'heading' => '📋 LAPORAN','class' => 'grid gap-1 mb-3']); ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'lapbrgmsk')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('lapbrgmsk'),'current' => request()->routeIs('lapbrgmsk'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('lapbrgmsk')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('lapbrgmsk')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Laporan Barang Masuk')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'lap-has-prod')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('lap-has-prod'),'current' => request()->routeIs('lap-has-prod'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('lap-has-prod')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('lap-has-prod')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Produksi Bulanan')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'lap-prod-minggu')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('lap-prod-minggu'),'current' => request()->routeIs('lap-prod-minggu'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('lap-prod-minggu')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('lap-prod-minggu')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Produksi Mingguan')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'lap-prod-hari')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('lap-prod-hari'),'current' => request()->routeIs('lap-prod-minggu'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('lap-prod-hari')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('lap-prod-minggu')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Produksi Harian')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'ketepatanwkt')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('ketepatanwkt'),'current' => request()->routeIs('ketepatanwkt'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ketepatanwkt')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('ketepatanwkt')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Ketepatan Waktu Selesai')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                    <?php if (\Illuminate\Support\Facades\Blade::check('menuitem', 'LAPORAN', 'ketepatanwktbln')): ?>
                        <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['icon' => 'home','href' => route('ketepatanwktbln'),'current' => request()->routeIs('ketepatanwktbln'),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'home','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ketepatanwktbln')),'current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('ketepatanwktbln')),'wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                            <?php echo e(__('Ketepatan Waktu Bulanan')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
            <?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalacac6a48a34186ea0abd369a00e5e2d4)): ?>
<?php $attributes = $__attributesOriginalacac6a48a34186ea0abd369a00e5e2d4; ?>
<?php unset($__attributesOriginalacac6a48a34186ea0abd369a00e5e2d4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalacac6a48a34186ea0abd369a00e5e2d4)): ?>
<?php $component = $__componentOriginalacac6a48a34186ea0abd369a00e5e2d4; ?>
<?php unset($__componentOriginalacac6a48a34186ea0abd369a00e5e2d4); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::spacer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::spacer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $attributes = $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $component = $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>
        
        <!-- Desktop User Menu -->
        <div class="px-3 pb-3">
            <?php if (isset($component)) { $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::dropdown','data' => ['position' => 'bottom','align' => 'start']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['position' => 'bottom','align' => 'start']); ?>
                <?php if (isset($component)) { $__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::profile','data' => ['name' => auth()->user()->name,'initials' => auth()->user()->initials(),'iconTrailing' => 'chevrons-up-down','class' => 'w-full hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-lg transition-all duration-300 rounded-xl p-3 border border-zinc-200 dark:border-zinc-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::profile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->name),'initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->initials()),'icon-trailing' => 'chevrons-up-down','class' => 'w-full hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-lg transition-all duration-300 rounded-xl p-3 border border-zinc-200 dark:border-zinc-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994)): ?>
<?php $attributes = $__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994; ?>
<?php unset($__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994)): ?>
<?php $component = $__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994; ?>
<?php unset($__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.index','data' => ['class' => 'w-[240px] bg-white dark:bg-zinc-800 shadow-2xl rounded-xl border border-zinc-200 dark:border-zinc-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-[240px] bg-white dark:bg-zinc-800 shadow-2xl rounded-xl border border-zinc-200 dark:border-zinc-700']); ?>
                    <?php if (isset($component)) { $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.radio.group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.radio.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="p-3 text-sm font-normal border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3 text-start">
                                <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-xl shadow-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-sm">
                                        <?php echo e(auth()->user()->initials()); ?>

                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-bold text-zinc-900 dark:text-white"><?php echo e(auth()->user()->name); ?></span>
                                    <span class="truncate text-xs text-zinc-500 dark:text-zinc-400"><?php echo e(auth()->user()->email); ?></span>
                                </div>
                            </div>
                        </div>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $attributes = $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $component = $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.radio.group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.radio.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <?php if (isset($component)) { $__componentOriginal5027d420cfeeb03dd925cfc08ae44851 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.item','data' => ['href' => route('settings.profile'),'icon' => 'cog','wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 transition-all duration-300 mx-2 my-1 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('settings.profile')),'icon' => 'cog','wire:navigate' => true,'class' => 'hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 transition-all duration-300 mx-2 my-1 rounded-lg']); ?>
                            <?php echo e(__('Settings')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $attributes = $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $component = $__componentOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $attributes = $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $component = $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginald5e1eb3ae521062f8474178ba08933ca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald5e1eb3ae521062f8474178ba08933ca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.separator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $attributes = $__attributesOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $component = $__componentOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__componentOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full p-2">
                        <?php echo csrf_field(); ?>
                        <?php if (isset($component)) { $__componentOriginal5027d420cfeeb03dd925cfc08ae44851 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.item','data' => ['as' => 'button','type' => 'submit','icon' => 'arrow-right-start-on-rectangle','class' => 'w-full hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300 rounded-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'submit','icon' => 'arrow-right-start-on-rectangle','class' => 'w-full hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300 rounded-lg']); ?>
                            <?php echo e(__('Log Out')); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $attributes = $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $component = $__componentOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
                    </form>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $attributes = $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $component = $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $attributes = $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $component = $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
        </div>
        
        
        <footer class="text-center text-xs text-zinc-400 dark:text-zinc-600 py-3 px-4 border-t border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-center gap-1.5">
                <span>©</span>
                <span><?php echo e(date('Y')); ?></span>
                <span>•</span>
                <span class="font-semibold">CV. Khasanah Sari</span>
            </div>
        </footer>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal17e56bc23bb0192e474b351c4358d446)): ?>
<?php $attributes = $__attributesOriginal17e56bc23bb0192e474b351c4358d446; ?>
<?php unset($__attributesOriginal17e56bc23bb0192e474b351c4358d446); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal17e56bc23bb0192e474b351c4358d446)): ?>
<?php $component = $__componentOriginal17e56bc23bb0192e474b351c4358d446; ?>
<?php unset($__componentOriginal17e56bc23bb0192e474b351c4358d446); ?>
<?php endif; ?>

    <!-- Mobile User Menu -->
    <?php if (isset($component)) { $__componentOriginale96c14d638c792103c11b984a4ed1896 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale96c14d638c792103c11b984a4ed1896 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::header','data' => ['class' => 'lg:hidden p-4 bg-white dark:bg-zinc-800 shadow-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:hidden p-4 bg-white dark:bg-zinc-800 shadow-md']); ?>
        <?php if (isset($component)) { $__componentOriginal1b6467b07b302021134396bbd98e74a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b6467b07b302021134396bbd98e74a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::sidebar.toggle','data' => ['class' => 'lg:hidden','icon' => 'bars-2','inset' => 'left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::sidebar.toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:hidden','icon' => 'bars-2','inset' => 'left']); ?>
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

        <?php if (isset($component)) { $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::spacer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::spacer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $attributes = $__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__attributesOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a)): ?>
<?php $component = $__componentOriginal4a4f7aa062a095c651c2f80bb685a42a; ?>
<?php unset($__componentOriginal4a4f7aa062a095c651c2f80bb685a42a); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::dropdown','data' => ['position' => 'top','align' => 'end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['position' => 'top','align' => 'end']); ?>
            <?php if (isset($component)) { $__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::profile','data' => ['initials' => auth()->user()->initials(),'iconTrailing' => 'chevron-down','class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md p-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::profile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['initials' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user()->initials()),'icon-trailing' => 'chevron-down','class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md p-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994)): ?>
<?php $attributes = $__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994; ?>
<?php unset($__attributesOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994)): ?>
<?php $component = $__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994; ?>
<?php unset($__componentOriginal2e5cdd03843a4c4d68fb9a6d7bd7e994); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.index','data' => ['class' => 'bg-white dark:bg-zinc-800 shadow-lg rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-white dark:bg-zinc-800 shadow-lg rounded-md']); ?>
                <?php if (isset($component)) { $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.radio.group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.radio.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    <?php echo e(auth()->user()->initials()); ?>

                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold"><?php echo e(auth()->user()->name); ?></span>
                                <span class="truncate text-xs"><?php echo e(auth()->user()->email); ?></span>
                            </div>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $attributes = $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $component = $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginald5e1eb3ae521062f8474178ba08933ca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald5e1eb3ae521062f8474178ba08933ca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.separator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $attributes = $__attributesOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $component = $__componentOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__componentOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.radio.group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.radio.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php if (isset($component)) { $__componentOriginal5027d420cfeeb03dd925cfc08ae44851 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.item','data' => ['href' => route('settings.profile'),'icon' => 'cog','wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('settings.profile')),'icon' => 'cog','wire:navigate' => true,'class' => 'hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Settings')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $attributes = $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $component = $__componentOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $attributes = $__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__attributesOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1)): ?>
<?php $component = $__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1; ?>
<?php unset($__componentOriginal48a7a6275c4dbe43f3b08c99bf9c2ce1); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginald5e1eb3ae521062f8474178ba08933ca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald5e1eb3ae521062f8474178ba08933ca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.separator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $attributes = $__attributesOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__attributesOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald5e1eb3ae521062f8474178ba08933ca)): ?>
<?php $component = $__componentOriginald5e1eb3ae521062f8474178ba08933ca; ?>
<?php unset($__componentOriginald5e1eb3ae521062f8474178ba08933ca); ?>
<?php endif; ?>

                <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal5027d420cfeeb03dd925cfc08ae44851 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::menu.item','data' => ['as' => 'button','type' => 'submit','icon' => 'arrow-right-start-on-rectangle','class' => 'w-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['as' => 'button','type' => 'submit','icon' => 'arrow-right-start-on-rectangle','class' => 'w-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md']); ?>
                        <?php echo e(__('Log Out')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $attributes = $__attributesOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__attributesOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851)): ?>
<?php $component = $__componentOriginal5027d420cfeeb03dd925cfc08ae44851; ?>
<?php unset($__componentOriginal5027d420cfeeb03dd925cfc08ae44851); ?>
<?php endif; ?>
                </form>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $attributes = $__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__attributesOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a)): ?>
<?php $component = $__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a; ?>
<?php unset($__componentOriginalf7749b857446d2788d0b6ca0c63f9d3a); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $attributes = $__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__attributesOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888)): ?>
<?php $component = $__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888; ?>
<?php unset($__componentOriginal2b4bb2cd4b8f1a3c08bae49ea918b888); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale96c14d638c792103c11b984a4ed1896)): ?>
<?php $attributes = $__attributesOriginale96c14d638c792103c11b984a4ed1896; ?>
<?php unset($__attributesOriginale96c14d638c792103c11b984a4ed1896); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale96c14d638c792103c11b984a4ed1896)): ?>
<?php $component = $__componentOriginale96c14d638c792103c11b984a4ed1896; ?>
<?php unset($__componentOriginale96c14d638c792103c11b984a4ed1896); ?>
<?php endif; ?>
    <?php echo $__env->make('components.layouts.app.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo e($slot); ?>

    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\hopusatrunningserverv3\resources\views/components/layouts/app/sidebar.blade.php ENDPATH**/ ?>