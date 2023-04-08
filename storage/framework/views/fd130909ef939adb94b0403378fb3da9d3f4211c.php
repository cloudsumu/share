<?php $__env->startSection('title'); ?>
    Admin - <?php echo e(__('crud.admin.maps.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
    <?php echo e(__('crud.admin.maps.index')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('startScripts'); ?>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-6">
        <div class="w-full overflow-x-auto">
            <div id="map" style="height: 500px"></div>
            <div id="legend" class="bg-gray-50 p-4 m-4 border-2">
                <h2 class="font-semibold text-lg mb-1">Note:</h2>
            </div>
        </div>
    </div>
    <style type="text/css">
        #legend img {
            display: inline;
        }
      </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('endScripts'); ?>
    <script>
        let map;

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 16,
                center: new google.maps.LatLng(<?php echo e($ref); ?>),
                mapTypeId: "roadmap",
            });
            const icons = {
                provider_available: {
                    name: "Provider Working",
                    icon: "<?php echo e(asset('img/map_icons/marker-provider-available.png')); ?>",
                },
                provider_inactive: {
                    name: "Provider Inactive",
                    icon: "<?php echo e(asset('img/map_icons/marker-provider-inactive.png')); ?>",
                },
                user: {
                    name: "User",
                    icon: "<?php echo e(asset('img/map_icons/marker-user.png')); ?>",
                },
            };
            const features = [
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    {
                        position: new google.maps.LatLng( <?php echo e($user->latitude); ?>, <?php echo e($user->longitude); ?>),
                        type: "user",
                    },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>

                <?php $__empty_1 = true; $__currentLoopData = $active_providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    {
                        position: new google.maps.LatLng(<?php echo e($p->s_latitude); ?>, <?php echo e($p->s_longitude); ?>),
                        type: "provider_available",
                    },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
                
                <?php $__empty_1 = true; $__currentLoopData = $inactive_providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    {
                        position: new google.maps.LatLng(<?php echo e($p->latitude); ?>, <?php echo e($p->longitude); ?>),
                        type: "provider_inactive",
                    },
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
            ];
            features.forEach((feature) => {
                new google.maps.Marker({
                    position: feature.position,
                    icon: icons[feature.type].icon,
                    map: map,
                });
            });
            const legend = document.getElementById("legend");

            for (const key in icons) {
                const type = icons[key];
                const name = type.name;
                const icon = type.icon;
                const div = document.createElement("div");
                div.innerHTML = '<img src="' + icon + '"> ' + name;
                legend.appendChild(div);
            }
            map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(config('constants.map_key')); ?>&callback=initMap&libraries=&v=weekly" async></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/map/index.blade.php ENDPATH**/ ?>