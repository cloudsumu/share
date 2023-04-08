<?php $attributes = $attributes->exceptProps([
    'name',
    'label',
    'value',
    'space' => NULL,
]); ?>
<?php foreach (array_filter(([
    'name',
    'label',
    'value',
    'space' => NULL,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="w-full px-4 mb-4 md:mb-0 <?php echo e($space ? $space : "md:w-1/2"); ?>">
    <div class="mb-6">
        <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.inputs.basic','data' => ['type' => 'text','name' => $name,'label' => ''.e($label ?? '').'','value' => $value,'attributes' => $attributes]]); ?>
<?php $component->withName('inputs.basic'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['type' => 'text','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'label' => ''.e($label ?? '').'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?> <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
    </div>
</div><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/inputs/text.blade.php ENDPATH**/ ?>