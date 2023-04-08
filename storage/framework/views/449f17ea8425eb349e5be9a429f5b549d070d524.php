<?php $__env->startSection('title'); ?>
    Admin - <?php echo e(__('crud.admin.disputes.type.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
    <?php echo e(__('crud.admin.disputes.type.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.indexPageSearch','data' => ['addBtnRoute' => route('admin.disputeType.create'),'addBtnText' => __('crud.admin.disputes.type.create')]]); ?>
<?php $component->withName('indexPageSearch'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['addBtnRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.disputeType.create')),'addBtnText' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('crud.admin.disputes.type.create'))]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-6">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                        <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.SNo')); ?></th>
                        <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.name')); ?></th>
                        <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.for')); ?></th>
                        <th class="text-center px-4 py-3"><?php echo e(__('crud.inputs.status')); ?></th>
                        <th class="text-center px-4 py-3"><?php echo e(__('crud.general.actions')); ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                    
                    <?php $__empty_1 = true; $__currentLoopData = $disputeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disputeType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="text-gray-700 dark:text-gray-400">
                            <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3"><?php echo e($loop->index + 1); ?></td>
                            <td class="px-4 dark:text-gray-400 dark:bg-gray-800 py-3 text-sm">
                                <?php echo e($disputeType->name ?? ""); ?>

                            </td>
                            <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3 text-sm capitalize">
                                <?php echo e($disputeType->for ?? "User"); ?>

                            </td>
                            <td class="text-center dark:text-gray-400 dark:bg-gray-800 px-4 py-3 text-sm">
                                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.show-status','data' => ['status' => $disputeType->status]]); ?>
<?php $component->withName('show-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($disputeType->status)]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                            </td>
                            <td>
                                <div class="flex items-center justify-center">
                                    
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.buttons.edit','data' => ['link' => route('admin.disputeType.edit', $disputeType)]]); ?>
<?php $component->withName('buttons.edit'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.disputeType.edit', $disputeType))]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.buttons.delete','data' => ['link' => route('admin.disputeType.destroy', $disputeType)]]); ?>
<?php $component->withName('buttons.delete'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['link' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.disputeType.destroy', $disputeType))]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
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
            <?php echo $disputeTypes->links(); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/disputeType/index.blade.php ENDPATH**/ ?>