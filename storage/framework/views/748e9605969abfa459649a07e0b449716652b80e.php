<?php $attributes = $attributes->exceptProps(['href']); ?>
<?php foreach (array_filter((['href']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php
    $isActive = ($active ?? false) ? 'bg-purple-600 ' : '';
?>

<li class="px-2 py-0.5 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
    <a class="w-full" href="<?php echo e($href); ?>"><?php echo e($slot); ?></a>
</li><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/nav/droplink.blade.php ENDPATH**/ ?>