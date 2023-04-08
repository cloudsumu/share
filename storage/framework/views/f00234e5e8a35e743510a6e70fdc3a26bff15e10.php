<header class="z-10 py-5 bg-white shadow-md dark:bg-gray-800">
    <div class="container flex items-center justify-between h-full px-6 mx-auto text-purple-600 dark:text-purple-300"
        style="justify-content: flex-end;">
        <!-- Mobile hamburger -->
        <button class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
            @click="toggleSideMenu" aria-label="Menu">
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
        </button>
        <!-- Search input -->
        
        <ul class="flex items-center flex-shrink-0 space-x-6">
            <!-- Theme toggler -->
            <li class="flex">
                <div class="relative inline-block text-left">
                    <div>
                        <?php
                        $locales = get_all_language();
                    ?>
                        <button x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" @click="toggleLangMenu" @keydown.escape="closeLangMenu" type="button"
                            class="bg-purple-50 dark:bg-gray-700 text-gray dark:text-white font-bold py-2 px-4 border-2  border-blue-500 hover:border-blue-500 rounded active:bg-purple-600 focus:outline-none focus:shadow-outline-purple"
                            id="menu-button" aria-expanded="true" aria-haspopup="true">
                            <?php $__currentLoopData = $locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(auth()->user()->language == $key): ?>
                            <?php echo e($value); ?>

                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                        </button>
                    </div>
                    <template x-if="isLangMenuOpen">
                        <div x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" @click.away="closeLangMenu"
                        @keydown.escape="closeLangMenu"
                        class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
                        aria-label="submenu">
                                <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->

                                <?php $__currentLoopData = $locales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <form method="GET" action="<?php echo e(route('admin.updateLanguage',$key)); ?>" role="none">
                                    <button type="submit" class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200" role="menuitem" tabindex="-1" id="menu-item-3">
                                        <?php echo e($value); ?>

                                      </button>
                                    </form>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                            </div>
                        </div>
                    </template>
                </div>
            </li>
            <?php if(config('constants.demo_mode', '') == '1'): ?>
                <li class="flex">
                    <button class="bg-purple-50 dark:bg-gray-700 text-gray dark:text-white font-bold py-2 px-4 border-2  border-blue-500 hover:border-blue-500 rounded active:bg-purple-600 focus:outline-none focus:shadow-outline-purple">
                        Demo Mode Active
                    </button>
                </li>
            <?php endif; ?>

            <li class="flex">
                <button class="rounded-md focus:outline-none focus:shadow-outline-purple" @click="toggleTheme"
                    aria-label="Toggle color mode">
                    <template x-if="!dark">
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </template>
                    <template x-if="dark">
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </template>
                </button>
            </li>
            <!-- Notifications menu -->
            
            <!-- Profile menu -->
            <li class="relative">
                <button class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none"
                    @click="toggleProfileMenu" @keydown.escape="closeProfileMenu" aria-label="Account"
                    aria-haspopup="true">
                    <img class="object-cover w-8 h-8 rounded-full" src="<?php echo e(asset('img/avatar.png')); ?>" alt=""
                        aria-hidden="true" />
                </button>
                <template x-if="isProfileMenuOpen">
                    <ul x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" @click.away="closeProfileMenu"
                        @keydown.escape="closeProfileMenu"
                        class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
                        aria-label="submenu">
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.profileSettings')); ?>">
                                <i class="fa fa-user mr-3"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <hr>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view settings')): ?>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.settings.otherSettings')); ?>">
                                <i class="fa fa-cog mr-3"></i>
                                <span><?php echo e(__('crud.navlinks.setting')); ?></span>
                            </a>
                        </li>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.settings.frontend.index')); ?>">
                                <i class="fa fa-picture-o mr-3"></i>
                                <span><?php echo e(__('crud.admin.settings.frontend')); ?> <?php echo e(__('crud.navlinks.setting')); ?></span>
                            </a>
                        </li>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.payment.setting')); ?>">
                                <p><?php echo e(config('constants.currency')); ?> &nbsp;&nbsp; &nbsp;</p> 
                                <span> <?php echo e(__('crud.payment.name')); ?> <?php echo e(__('crud.navlinks.setting')); ?></span>
                            </a>
                        </li>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.settings.tnc')); ?>">
                                <i class="fa fa-legal mr-3"></i>
                                <span><?php echo e(__('crud.general.tnc')); ?></span>
                            </a>
                        </li>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.settings.privacy')); ?>">
                                <i class="fa fa-shield mr-3"></i>
                                <span><?php echo e(__('crud.general.privacy_policy')); ?></span>
                            </a>
                        </li>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.translations')); ?>">
                                <i class="fa fa-exchange mr-3"></i>
                                <span><?php echo e(__('crud.navlinks.translation')); ?></span>
                            </a>
                        </li>
                        <hr>
                        <?php endif; ?>
                        <?php if(auth()->guard('admin')->check()): ?>
                        <li class="flex">
                            <a class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                href="<?php echo e(route('admin.logout')); ?>">
                                <i class="fa fa-sign-out mr-3"></i>
                                <span>Log out</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </template>
            </li>
        </ul>
    </div>
</header><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/layout/header.blade.php ENDPATH**/ ?>