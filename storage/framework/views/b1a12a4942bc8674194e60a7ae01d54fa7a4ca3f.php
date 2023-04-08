<?php $attributes = $attributes->exceptProps([
'space' => null,
'status' => null,
'label' => null,
'name' => null,
]); ?>
<?php foreach (array_filter(([
'space' => null,
'status' => null,
'label' => null,
'name' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.inputs.select','data' => ['label' => $label ?? __('crud.inputs.status'),'name' => ''.e($name ?? 'status').'','space' => $space,'id' => 'status']]); ?>
<?php $component->withName('inputs.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label ?? __('crud.inputs.status')),'name' => ''.e($name ?? 'status').'','space' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($space),'id' => 'status']); ?>
    <option <?php echo e($status == "1" ? "selected" : ''); ?> value="1">Enabled</option>
    <option <?php echo e($status == "0" ? "selected" : ''); ?> value="0">Disabled</option>
 <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/inputs/status.blade.php ENDPATH**/ ?>