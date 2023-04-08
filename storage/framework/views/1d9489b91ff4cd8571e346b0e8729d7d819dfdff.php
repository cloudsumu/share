<?php $attributes = $attributes->exceptProps(['link']); ?>
<?php foreach (array_filter((['link']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<a href="<?php echo e($link); ?>" class="bg-green-400 text-white rounded-full flex w-8 h-8 justify-center items-center hover:bg-green-500 mx-1">
    <i class="fa fa-pencil"></i>
</a><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/buttons/edit.blade.php ENDPATH**/ ?>