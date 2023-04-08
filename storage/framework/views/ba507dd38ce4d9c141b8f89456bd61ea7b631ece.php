<?php $__env->startSection('title'); ?>
    Admin - <?php echo e(__('crud.admin.disputes.type.create')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
    <?php echo e(__('crud.admin.disputes.type.create')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full rounded-lg shadow-xs bg-white dark:text-gray-400 dark:bg-gray-800 mb-5">
        <div class="w-full px-5 py-5">
            <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.form','data' => ['action' => ''.e(route('admin.disputeType.store')).'','method' => 'post','hasFile' => true]]); ?>
<?php $component->withName('form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['action' => ''.e(route('admin.disputeType.store')).'','method' => 'post','has-file' => true]); ?>
                <?php echo $__env->make('admin.disputeType.form-inputs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                
                <div class="flex justify-end">
                    <button type="submit" class="right-0 inline-block py-1 px-4 leading-loose bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 text-sm" type="submit">Add Dispute Type</button>
                </div>
             <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/disputeType/create.blade.php ENDPATH**/ ?>