<?php 
    $editing = isset($disputeType);
?>

<div class="flex flex-wrap -mx-4 -mb-4 md:mb-0">
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.inputs.text','data' => ['name' => 'name','label' => __('crud.admin.disputes.type.name'),'value' => ''.e(old('name', ($editing ? $disputeType->name : ''))).'']]); ?>
<?php $component->withName('inputs.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['name' => 'name','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('crud.admin.disputes.type.name')),'value' => ''.e(old('name', ($editing ? $disputeType->name : ''))).'']); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.inputs.select','data' => ['label' => __('crud.admin.disputes.type.name').' '.__('crud.inputs.for'),'name' => 'for']]); ?>
<?php $component->withName('inputs.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('crud.admin.disputes.type.name').' '.__('crud.inputs.for')),'name' => 'for']); ?>
        <option <?php echo e(old('for', ($editing ? $disputeType->for : '')) == "user" ? "selected" : ''); ?> value="user">User</option>
        <option <?php echo e(old('for', ($editing ? $disputeType->for : '')) == "agent" ? "selected" : ''); ?> value="agent">Agent</option>
        <option <?php echo e(old('for', ($editing ? $disputeType->for : '')) == "provider" ? "selected" : ''); ?> value="provider">Provider</option>
     <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.inputs.status','data' => ['status' => ''.e(old('status', ($editing ? $disputeType->status : ''))).'']]); ?>
<?php $component->withName('inputs.status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['status' => ''.e(old('status', ($editing ? $disputeType->status : ''))).'']); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
</div><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/disputeType/form-inputs.blade.php ENDPATH**/ ?>