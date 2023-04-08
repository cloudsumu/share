<?php $attributes = $attributes->exceptProps([
'name',
'label',
'type' => 'text',
'space' => null,
]); ?>
<?php foreach (array_filter(([
'name',
'label',
'type' => 'text',
'space' => null,
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
        <?php if($label ?? null): ?>
        <?php echo $__env->make('components.inputs.partials.label', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        <div class="relative">
            <select
                class="appearance-none w-full p-4 text-xs font-semibold leading-none bg-gray-50 rounded outline-none dark:bg-gray-700 dark:text-gray-300"
                id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" <?php echo e(($required ?? false) ? 'required' : ''); ?> autocomplete="off"
                <?php echo e($attributes); ?>>
                <?php echo e($slot); ?>

            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"></path>
                </svg>
            </div>
        </div>
    </div>
</div><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/inputs/select.blade.php ENDPATH**/ ?>