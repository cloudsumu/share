<?php $attributes = $attributes->exceptProps(['active', 'title', 'href', 'icon']); ?>
<?php foreach (array_filter((['active', 'title', 'href', 'icon']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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
    <div x-data="{ <?php echo e($href); ?> : <?php echo e($active ? "true" : "false"); ?> }">
        <span class="<?php echo e($isActive); ?>absolute inset-y-0 left-0 w-1 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
        <button class="<?php echo e($isActiveColor); ?>inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200" @click="<?php echo e($href); ?>=!<?php echo e($href); ?>" aria-haspopup="true">
            <span class="inline-flex items-center">
                <i class="fa hover:text-gray-800 dark:hover:text-gray-200 fas fa-<?php echo e($icon); ?>" style="font-size: 1.2rem;"></i>
                <span class="hover:text-gray-800 dark:hover:text-gray-200 ml-4"><?php echo e($title); ?></span>
            </span>
            <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
        <div x-show="<?php echo e($href); ?>">
            <ul
                x-transition:enter="transition-all ease-in-out duration-300"
                x-transition:enter-start="opacity-25 max-h-0"
                x-transition:enter-end="opacity-100 max-h-xl"
                x-transition:leave="transition-all ease-in-out duration-300"
                x-transition:leave-start="opacity-100 max-h-xl"
                x-transition:leave-end="opacity-0 max-h-0"
                class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900" 
                aria-label="submenu"
            >
                <?php echo e($slot); ?>

            </ul>
        </div>
    </div>
</li><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/nav/droplist.blade.php ENDPATH**/ ?>