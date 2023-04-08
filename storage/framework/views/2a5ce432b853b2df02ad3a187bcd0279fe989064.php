<!DOCTYPE html>
<html lang="en">
    <head>
        <title>
            <?php echo $__env->yieldContent('title', config('constants.site_title', '')); ?>
        </title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700;900&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" href="<?php echo e(asset('css/new_user_layout/tailwind.min.css')); ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('storage/'.config('constants.site_icon', ''))); ?>">
        <script src="<?php echo e(asset('js/new_main.js')); ?>"></script>
    </head>
    <body class="antialiased bg-body text-body font-body">
        <div class="">
            <?php echo $__env->make('layout.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
            <?php echo $__env->make('layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </body>
</html><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/layout/app.blade.php ENDPATH**/ ?>