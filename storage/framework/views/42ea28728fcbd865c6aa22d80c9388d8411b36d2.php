<?php $attributes = $attributes->exceptProps([
'status'
]); ?>
<?php foreach (array_filter(([
'status'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if($status == "1"): ?>
<span
    class="px-2 py-1 font-semibold leading-tight text-green-600 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100 text-xs">
    Enabled
</span>
<?php elseif($status == "0"): ?>
<span
    class="px-2 py-1 font-semibold leading-tight text-red-600 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-700 text-xs">
    Disabled
</span>
<?php endif; ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/show-status.blade.php ENDPATH**/ ?>