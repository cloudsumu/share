<?php $attributes = $attributes->exceptProps([
    'addBtnRoute' => '#',
    'addBtnText',
    'showCreate' => true,
    'showSearch' => true,
]); ?>
<?php foreach (array_filter(([
    'addBtnRoute' => '#',
    'addBtnText',
    'showCreate' => true,
    'showSearch' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div class="grid grid-cols-3 gap-4 justify-between">
    <?php if($showSearch): ?>
        <div class="col-span-2">
            <form>
                <div class="relative text-gray-500 mb-5">
                    <input class="block rounded-l border border-gray-200 w-full pr-20 mt-1 text-sm text-black dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none dark:focus:shadow-outline-gray" placeholder="Enter Search Value" style="padding: 8px;" name="search" value="<?php echo e(request()->has('search') ? request()->get('search') : ''); ?>">
                    <button class="absolute inset-y-0 right-0 px-4 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-r-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none">
                        <?php echo e(__('crud.general.search')); ?>

                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    <div class="col-span-1 mb-5"style="display: flex; align-items: flex-end; justify-content: flex-end;">
        <?php if($showCreate): ?>
            <a href="<?php echo e($addBtnRoute); ?>" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                <i class="fa fa-plus"></i> <?php echo e($addBtnText); ?>

            </a>
        <?php endif; ?>
    </div>
</div><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/components/indexPageSearch.blade.php ENDPATH**/ ?>