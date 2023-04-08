<section class="relative bg-indigo-700 overflow-x-hidden">
    <div class="container px-4 mx-auto">
        <nav class="flex justify-between items-center py-4">
            <a class="filter brightness-0 filter invert text-2xl leading-none" href="<?php echo e(route('home')); ?>">
                <img class="filter brightness-0 filter invert h-20"
                    src="<?php echo e(url('storage/'.config('constants.site_logo'))); ?>" alt="" width="auto" style="
    filter: brightness(0) invert(1);
">
            </a>
            
    </nav>
    </div>

    <div class="hidden navbar-menu relative z-50">
        <div class="navbar-backdrop fixed inset-0 bg-gray-800 opacity-25"></div>
        <nav
            class="fixed top-0 left-0 bottom-0 flex flex-col w-5/6 max-w-sm py-6 px-6 bg-white border-r overflow-y-auto">
            <div class="flex items-center mb-8">
                <a class="mr-auto text-2xl font-semibold leading-none" href="#">
                    <img class="filter brightness-0 filter invert h-20"
                        src="<?php echo e(url('storage/'.config('constants.site_logo'))); ?>" alt="" width="auto" style="
                                        filter: brightness(0) invert(1);
                                    ">
                </a>
                <button class="navbar-close">
                    <svg class="h-6 w-6 text-gray-400 cursor-pointer hover:text-gray-500"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div>
                <ul>
                    <li class="mb-1"><a class="block p-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 rounded"
                            href="#">About</a></li>
                    <li class="mb-1"><a class="block p-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 rounded"
                            href="#">Company</a></li>
                    <li class="mb-1"><a class="block p-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 rounded"
                            href="#">Services</a></li>
                    <li class="mb-1"><a class="block p-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 rounded"
                            href="#">Testimonials</a></li>
                </ul>
            </div>
            <div class="mt-auto">
                <div class="pt-6">
                    <a class="block px-6 py-2 mb-3 text-sm text-center text-gray-500 hover:text-gray-600 font-bold leading-loose border border-gray-100 hover:border-gray-200 rounded"
                        href="#">
                        Sign in
                    </a>
                    <a class="block px-6 py-2 mb-2 text-sm text-center text-gray-500 hover:text-gray-600 font-bold leading-loose border border-gray-100 hover:border-gray-200 rounded"
                        href="#">
                        Sign up
                    </a>
                </div>
                <p class="mt-6 mb-4 text-sm text-center text-gray-400">
                    <span>© 2021 All rights reserved.</span>
                </p>
            </div>
        </nav>
    </div>
</section><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/layout/nav.blade.php ENDPATH**/ ?>