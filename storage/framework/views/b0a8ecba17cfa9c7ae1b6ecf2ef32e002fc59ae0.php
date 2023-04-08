<?php $attributes = $attributes->exceptProps([
    'name',
    'label',
    'value',
    'type',
    'min' => null,
    'max' => null,
    'step' => null,
    'idSuff' => null,
    'showError' => false,
]); ?>
<?php foreach (array_filter(([
    'name',
    'label',
    'value',
    'type',
    'min' => null,
    'max' => null,
    'step' => null,
    'idSuff' => null,
    'showError' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($label ?? null): ?>
    <?php echo $__env->make('components.inputs.partials.label', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>

<input
    type="<?php echo e($type); ?>"
    id="<?php echo e($name); ?><?php echo e($idSuff); ?>"
    name="<?php echo e($name); ?>"
    value="<?php echo e(old($name, $value ?? '')); ?>"
    <?php echo e(($required ?? false) ? 'required' : ''); ?>

    <?php echo e(isset($min) ? "min={$min}" : ''); ?>

    <?php echo e(isset($max) ? "max={$max}" : ''); ?>

    <?php echo e($step ? "step={$step}" : ''); ?>

    autocomplete="off"
    <?php echo e($attributes); ?>

    class="appearance-none w-full p-4 text-xs font-semibold leading-none rounded outline-none bg-gray-50 dark:bg-gray-700 dark:text-gray-300"
    placeholder="Enter <?php echo e($label); ?>"
>
<?php if($showError): ?>
    <?php $__errorArgs = ["$name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="dark:text-red-400 text-red-600 font-semibold text-sm error">** <?php echo e($message); ?> **</span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
<?php endif; ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/inputs/basic.blade.php ENDPATH**/ ?>