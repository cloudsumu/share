<?php $attributes = $attributes->exceptProps(['active', 'href', 'icon']); ?>
<?php foreach (array_filter((['active', 'href', 'icon']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php
    $isActive = ($active ?? false) ? 'bg-purple-600 ' : '';
    $isActiveColor = ($active ?? false) ? 'dark:text-gray-100 text-gray-800 ' : '';
?>

<li class="relative px-6 py-1.5">
    <span class="<?php echo e($isActive); ?>absolute inset-y-0 left-0 w-1 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
    <a class="<?php echo e($isActiveColor); ?>inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" href="<?php echo e($href); ?>">
        <i class="fa fa-<?php echo e($icon); ?>" style="font-size: 1.2rem;"></i>
        <span class="ml-3">
            <?php echo e($slot); ?>

        </span>
    </a>
</li><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/nav/navlink.blade.php ENDPATH**/ ?>