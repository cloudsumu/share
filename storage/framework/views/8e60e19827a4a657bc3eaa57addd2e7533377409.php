<?php $attributes = $attributes->exceptProps([
    'extraClasses' => NULL,
    'name',
    'label'
]); ?>
<?php foreach (array_filter(([
    'extraClasses' => NULL,
    'name',
    'label'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<label class="<?php echo e($extraClasses); ?> dark:text-gray-400 block text-gray-800 text-sm font-semibold mb-2" for="<?php echo e($name); ?>">
    <?php echo e($label); ?>

</label><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/inputs/partials/label.blade.php ENDPATH**/ ?>