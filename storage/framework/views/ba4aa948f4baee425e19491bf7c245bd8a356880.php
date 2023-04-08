<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="flex items-center justify-between border-t dark:border-gray-700 dark:bg-gray-800">
        <div class="flex justify-between flex-1 sm:hidden">
            <?php if($paginator->onFirstPage()): ?>
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    <?php echo __('pagination.previous'); ?>

                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    <?php echo __('pagination.previous'); ?>

                </a>
            <?php endif; ?>

            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                    <?php echo __('pagination.next'); ?>

                </a>
            <?php else: ?>
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    <?php echo __('pagination.next'); ?>

                </span>
            <?php endif; ?>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div class="px-4 py-4 text-xs font-semibold tracking-wide text-gray-500 dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
                <p class="flex items-center col-span-3">
                    <?php echo __('Showing'); ?> <?php echo e($paginator->firstItem()); ?>-<?php echo e($paginator->lastItem()); ?> <?php echo __('of'); ?> <?php echo e($paginator->total()); ?>

                </p>
            </div>

            <div>
                <span class="inline-flex items-center">
                    
                    <?php if($paginator->onFirstPage()): ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <span class="px-3 py-1 rounded-md text-xs rounded-l-lg focus:outline-none focus:shadow-outline-purple" aria-hidden="true">
                                <i class="fa fa-angle-left"></i>
                            </span>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="px-3 py-1 rounded-md text-xs rounded-l-lg focus:outline-none focus:shadow-outline-purple" aria-label="<?php echo e(__('pagination.previous')); ?>">
                            <i class="fa fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    
                    <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        
                        <?php if(is_string($element)): ?>
                            <span aria-disabled="true">
                                <span class="px-3 py-1"><?php echo e($element); ?></span>
                            </span>
                        <?php endif; ?>
                        
                        <?php if(is_array($element)): ?>
                            <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $paginator->currentPage()): ?>
                                    <button class="px-3 py-1 text-white transition-colors duration-150 bg-purple-600 border border-r-0 border-purple-600 rounded-md text-xs focus:outline-none focus:shadow-outline-purple"><?php echo e($page); ?></button>
                                <?php else: ?>
                                    <a href="<?php echo e($url); ?>" class="px-3 py-1 rounded-md focus:outline-none text-xs focus:shadow-outline-purple text-gray-500" aria-label="<?php echo e(__('Go to page :page', ['page' => $page])); ?>">
                                        <?php echo e($page); ?>

                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php if($paginator->hasMorePages()): ?>
                        <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="px-3 py-1 rounded-md rounded-l-lg text-xs focus:outline-none focus:shadow-outline-purple" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <i class="fa fa-angle-right"></i>
                        </a>
                    <?php else: ?>
                        <span aria-disabled="true" aria-label="<?php echo e(__('pagination.next')); ?>">
                            <span class="px-3 text-xs py-1 rounded-md rounded-l-lg focus:outline-none focus:shadow-outline-purple" aria-hidden="true">
                                <i class="fa fa-angle-right"></i>
                            </span>
                        </span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>