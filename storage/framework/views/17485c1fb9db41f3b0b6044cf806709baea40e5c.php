

<?php $__env->startSection('title'); ?>
Admin - <?php echo e(__('crud.admin.serviceTypes.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
<?php echo e(__('crud.admin.serviceTypes.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.indexPageSearch','data' => ['addBtnRoute' => route('admin.serviceType.create'),'addBtnText' => __('crud.admin.serviceTypes.create')]]); ?>
<?php $component->withName('indexPageSearch'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['addBtnRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.serviceType.create')),'addBtnText' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('crud.admin.serviceTypes.create'))]); ?>
 <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<div class="w-full overflow-hidden rounded-lg shadow-xs mb-6">
    <div class="w-full overflow-x-auto">
        <table class="w-full whitespace-no-wrap">
            <thead>
                <tr
                    class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.SNo')); ?></th>
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.name')); ?></th>
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.description')); ?></th>
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.status')); ?></th>
                    <th class="text-center px-4 py-3"><?php echo e(__('crud.general.actions')); ?></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                
                <?php
                    $i = 0;
                ?>
                
                <?php $__empty_1 = true; $__currentLoopData = $serviceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="text-gray-700 dark:text-gray-400">
                        <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3"><?php echo e($i); ?></td>
                        <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-sm">
                            <div class="flex items-center text-sm">
                                <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                    <img class="object-cover w-full h-full rounded-full"
                                        src="<?php echo e($serviceType->image ? asset('storage/'.$serviceType->image) : asset('img/avatar.png')); ?>"
                                        alt="" loading="lazy" />
                                    <div class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></div>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700 dark:text-gray-400"><?php echo e($serviceType->name); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-sm">
                            <p class="font-semibold text-gray-700 dark:text-gray-400">
                                <?php echo e(Str::limit($serviceType->description, 70)); ?></p>
                        </td>
                        <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-sm text-center">
                            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.show-status','data' => ['status' => $serviceType->status]]); ?>
<?php $component->withName('show-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($serviceType->status)]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                        </td>
                        <td>
                            <div class="flex items-center justify-center">
                                
                                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.buttons.edit','data' => ['link' => route('admin.serviceType.edit', $serviceType)]]); ?>
<?php $component->withName('buttons.edit'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.serviceType.edit', $serviceType))]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.buttons.delete','data' => ['link' => route('admin.serviceType.destroy', $serviceType)]]); ?>
<?php $component->withName('buttons.delete'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.serviceType.destroy', $serviceType))]); ?>
                                 <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                                
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td class="text-center dark:text-gray-400 dark:bg-gray-800 py-3 text-sm" colspan="10">
                            <?php echo app('translator')->get('crud.general.not_found'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="">
        <?php echo $serviceTypes->links(); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/serviceType/index.blade.php ENDPATH**/ ?>