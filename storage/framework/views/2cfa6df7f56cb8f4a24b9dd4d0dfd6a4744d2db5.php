<footer class="py-20 border-t border-gray-100">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap -mx-4 mb-8 lg:mb-16">
            <div class="w-full lg:w-2/3 px-4 mb-12 lg:mb-0 flex justify-center">
                <a class="text-gray-600 text-2xl leading-none mr-2" href="<?php echo e(route('home')); ?>">
                    <img src="<?php echo e(asset('storage/'.config('constants.site_icon', ''))); ?>" alt="" width="auto" style="max-width: 225px; max-height: 120px;">
                </a>
                <div>
                    <p class="mt-5 mb-6 max-w-xs text-gray-500 leading-loose"><?php echo e(__('2021 come up with lot off challenges.')); ?></p>
                    <a class="inline-block h-6 mr-8" href="<?php echo e(config('constants.facebook_link', '')); ?>">
                        <img class="mx-auto" src="<?php echo e(asset('img/socials/facebook.svg')); ?>">
                    </a>
                    <a class="inline-block h-6 mr-8" href="<?php echo e(config('constants.instagram_link', '')); ?>">
                        <img class="mx-auto" src="<?php echo e(asset('img/socials/instagram.svg')); ?>">
                    </a>
                    <a class="inline-block h-6" href="<?php echo e(config('constants.twitter_link', '')); ?>">
                        <img class="mx-auto" src="<?php echo e(asset('img/socials/twitter.svg')); ?>">
                    </a>
                </div>
            </div>
            <div class="w-full lg:w-1/3 px-4">
                <div class="block">
                    
                    
                    <div class="mb-8 lg:mb-0">
                        <h3 class="mb-6 text-lg font-bold font-heading"><?php echo e(__('Legal')); ?></h3>
                        <ul class="text-sm">
                            <li class="mb-4">
                                <a class="text-gray-500 hover:text-gray-600" href="<?php echo e(route('tnc')); ?>"><?php echo e(__('Terms & Conditions')); ?></a>
                            </li>
                            <li class="mb-4">
                                <a class="text-gray-500 hover:text-gray-600" href="<?php echo e(route('privacy')); ?>"><?php echo e(__('Privacy Policy')); ?></a>
                            </li>
                            <li class="mb-4">
                                <a class="text-gray-500 hover:text-gray-600" href="<?php echo e(route('faq')); ?>"><?php echo e(__('FAQ')); ?>s</a>
                            </li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="border-t border-gray-50 pt-8">
            <p class="lg:text-center text-sm text-gray-400">
                <?php echo e(config('constants.site_copyright', 'All rights reserved © ThinkinDragon 2021')); ?></p>
        </div>
    </div>
</footer><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/layout/footer.blade.php ENDPATH**/ ?>