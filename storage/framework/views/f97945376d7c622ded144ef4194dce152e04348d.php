<!DOCTYPE html>
<html :class="{ 'dark': dark }" x-data="data()" lang="en" tranaslate="no">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?php echo e(url('storage/'.config('constants.site_icon'))); ?>" type="image/gif" sizes="16x16">
    
    <title>
        <?php echo $__env->yieldContent('title', 'Admin Dasboard'); ?>
    </title>

    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/normalize.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('css/notyf.css')); ?>">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/font-awesome.min.css')); ?>">
    <?php echo \Livewire\Livewire::styles(); ?>

    
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="<?php echo e(asset('js/init-alpine.js')); ?>"></script>

    
    <script src="<?php echo e(asset('js/notyf.js')); ?>"></script>
    <?php echo \Livewire\Livewire::scripts(); ?>


    <script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script>
    
    <script src="<?php echo e(asset('js/main.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('startScripts'); ?>
</head>

<body>
    <style>
        #loading {
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            position: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: black;
            z-index: 99;
            text-align: center;
        }

        #loading-image {
            position: absolute;
        }
    </style>
    
    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        
        <?php echo $__env->make('admin.layout.aside', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="flex flex-col flex-1 w-full">

            
            <?php echo $__env->make('admin.layout.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <main>
                <div class="container px-6 mx-auto grid">
                    
                    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                        <?php echo $__env->yieldContent('heading'); ?>
                    </h2>
                    <?php echo $__env->yieldContent('content'); ?>
                </div>

            </main>

            
            <?php echo $__env->make('admin.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
    

    <script>
        const notyf = new Notyf({
                duration: 2500,
                position: {
                    x: 'right',
                    y: 'top',
                },
                dismissible: true,
            });
            <?php if($errors->any()): ?>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    notyf.error("<?php echo e($error); ?>");
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <?php if(session()->has('success')): ?>
                notyf.success("<?php echo e(session()->get('success')); ?>");
            <?php endif; ?>
    </script>



    
    <?php echo $__env->yieldPushContent('endScripts'); ?>

    <script>
        // Listening for livewire event.
            Livewire.on('livewire_success', function(msg) {
                notyf.success(msg);
            });
            Livewire.on('livewire_error', function(msg) {
                notyf.error(msg);
            });
    </script>
</body>

</html><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/layout/app.blade.php ENDPATH**/ ?>