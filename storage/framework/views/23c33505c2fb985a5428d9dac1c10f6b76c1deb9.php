<?php $__env->startSection('content'); ?>
<section class="relative bg-indigo-700 overflow-hidden">
    <div class="container px-4 mx-auto">
        <div class="mt-16 lg:mb-24 max-w-md pb-16">
            <div class="max-w-2xl lg:max-w-md mb-6">
                <h2 class="mb-8 text-4xl md:text-5xl text-white font-bold font-heading">
                    <?php echo e(array_key_exists('home_heading', $settings) ? $settings['home_heading'] : "Take care of your performance every day."); ?>

                </h2>
                <p class="text-lg text-gray-200 leading-loose">
                    <?php echo e(array_key_exists('home_tag_line', $settings) ? $settings['home_tag_line'] : "Build a well-presented brand that everyone will love. Take care to develop resources continually and integrity them with previous projects."); ?>

                </p>
            </div>
            
        </div>
    </div>
    <div
        class="lg:absolute lg:right-0 lg:top-1/2 mt-16 lg:mt-4 lg:-mr-8 lg:transform lg:-translate-y-1/2 w-full lg:w-1/2 px-4 lg:pb-0 pb-16">
        <img class="mx-auto lg:mx-0 lg:ml-auto w-full h-80 lg:h-112 object-cover rounded-lg"
            src="<?php echo e(array_key_exists('home_side_image', $settings) ? asset('storage/'.$settings['home_side_image']) : asset('img/gray-400-horizontal.png')); ?>"
            alt="">
    </div>
</section>

<section class="py-20">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap -mx-4">
            <?php for($i = 0; $i < 3; $i++): ?> <div class="w-full lg:w-1/3 px-4 mb-12 lg:mb-0 text-center">
                <span
                    class="inline-block mx-auto mb-6 flex items-center justify-center <?php if(!array_key_exists('home_step_image'.($i+1), $settings)): ?> bg-gray-400 <?php endif; ?> rounded-full w-16 h-16">
                    <?php if(array_key_exists('home_step_image'.($i+1), $settings)): ?>
                    <img src="<?php echo e(asset('storage/'.$settings['home_step_image'.($i+1)])); ?>" alt="" width="50"
                        height="50">
                    <?php else: ?>
                    <svg class="text-gray-50" width="32" height="32" viewbox="0 0 32 32" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M18.1332 14.5333C18.2665 14.6667 18.3998 14.6667 18.6665 14.6667H27.9998C28.7998 14.6667 29.3332 14.1333 29.3332 13.3333C29.3332 13.0667 29.3332 12.9333 29.1998 12.8L24.5332 3.46667C24.1332 2.8 23.3332 2.53334 22.6665 2.93334C22.5332 3.06667 22.2665 3.2 22.1332 3.46667L17.4665 12.8C17.1998 13.3333 17.4665 14.1333 18.1332 14.5333ZM23.3332 6.93334L25.8665 12H20.7998L23.3332 6.93334ZM8.6665 2.66667C5.33317 2.66667 2.6665 5.33334 2.6665 8.66667C2.6665 12 5.33317 14.6667 8.6665 14.6667C11.9998 14.6667 14.6665 12 14.6665 8.66667C14.6665 5.33334 11.9998 2.66667 8.6665 2.66667ZM8.6665 12C6.79984 12 5.33317 10.5333 5.33317 8.66667C5.33317 6.80001 6.79984 5.33334 8.6665 5.33334C10.5332 5.33334 11.9998 6.80001 11.9998 8.66667C11.9998 10.5333 10.5332 12 8.6665 12ZM14.2665 17.7333C13.7332 17.2 12.9332 17.2 12.3998 17.7333L8.6665 21.4667L4.93317 17.7333C4.39984 17.2 3.59984 17.2 3.0665 17.7333C2.53317 18.2667 2.53317 19.0667 3.0665 19.6L6.79984 23.3333L3.0665 27.0667C2.53317 27.6 2.53317 28.4 3.0665 28.9333C3.59984 29.4667 4.39984 29.4667 4.93317 28.9333L8.6665 25.2L12.3998 28.9333C12.9332 29.4667 13.7332 29.4667 14.2665 28.9333C14.7998 28.4 14.7998 27.6 14.2665 27.0667L10.5332 23.3333L14.2665 19.6C14.7998 19.0667 14.7998 18.2667 14.2665 17.7333ZM27.9998 17.3333H18.6665C17.8665 17.3333 17.3332 17.8667 17.3332 18.6667V28C17.3332 28.8 17.8665 29.3333 18.6665 29.3333H27.9998C28.7998 29.3333 29.3332 28.8 29.3332 28V18.6667C29.3332 17.8667 28.7998 17.3333 27.9998 17.3333ZM26.6665 26.6667H19.9998V20H26.6665V26.6667Z"
                            fill="CurrentColor"></path>
                    </svg>
                    <?php endif; ?>
                </span>
                <h3 class="mb-4 text-2xl font-bold font-heading">
                    <?php echo e(array_key_exists('home_step_title'.($i+1), $settings) ? $settings['home_step_title'.($i+1)] : "Change of access"); ?>

                </h3>
                <p class="text-gray-300 leading-loose max-w-sm mx-auto lg:px-12">
                    <?php echo e(array_key_exists('home_step_desc'.($i+1), $settings) ? $settings['home_step_desc'.($i+1)] : "Take care to develop resources continually and integrity them with previous projects."); ?>

                </p>
        </div>
        <?php endfor; ?>
    </div>
    </div>
</section>

<section class="py-20">
    <div class="container px-4 mx-auto">
        <div class="flex flex-wrap items-center -mx-4">
            <div class="w-full md:w-1/3 lg:w-1/2 px-16 md:px-4 order-last md:order-first">
                <img class="mx-auto"
                    src="<?php echo e(array_key_exists('home_app_image', $settings) ? asset('storage/'.$settings['home_app_image']) : asset('img/2-devices-left.svg ')); ?>"
                    alt="">
            </div>
            <div class="w-full md:w-2/3 lg:w-1/2 px-4 mb-16 md:mb-0">
                <div class="lg:max-w-2xl lg:ml-auto">
                    <h2 class="mb-4 text-4xl lg:text-5xl font-bold font-heading">
                        <?php echo e(array_key_exists('home_app_title', $settings) ? $settings['home_app_title'] : "Get our Free Mobile App"); ?>

                    </h2>
                    <p class="mb-4 text-lg text-gray-500 leading-loose">
                        <?php echo e(array_key_exists('home_app_desc', $settings) ? $settings['home_app_desc'] : "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque massa nibh, pulvinar vitae aliquet nec, accumsan aliquet orci."); ?>

                    </p>
                    <div class="text-xl text-gray-700 font-semibold">
                        User Apps
                    </div>
                    <div class="flex justify-start items-center">
                        <?php if(config('constants.store_link_ios_user') != '#'): ?>
                        <a class="mr-4" href="<?php echo e(config('constants.store_link_ios_user')); ?>">
                            <img src="<?php echo e(asset('img/app-store.svg')); ?>" alt="">
                        </a>
                        <?php endif; ?>
                        <?php if(config('constants.store_link_android_user') != '#'): ?>
                        <a href="<?php echo e(config('constants.store_link_android_user')); ?>">
                            <img src="<?php echo e(asset('img/google-play.svg')); ?>" alt="">
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="text-xl text-gray-700 mt-3 font-semibold">
                        Provider Apps
                    </div>
                    <div class="flex justify-start items-center">
                        <?php if(config('constants.store_link_ios_provider') != '#'): ?>
                        <a class="mr-4" href="<?php echo e(config('constants.store_link_ios_provider')); ?>">
                            <img src="<?php echo e(asset('img/app-store.svg')); ?>" alt="">
                        </a>
                        <?php endif; ?>
                        <?php if(config('constants.store_link_android_provider') != '#'): ?>
                        <a href="<?php echo e(config('constants.store_link_android_provider')); ?>">
                            <img src="<?php echo e(asset('img/google-play.svg')); ?>" alt="">
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/index.blade.php ENDPATH**/ ?>