<?php $__env->startSection('title'); ?>
    Admin - <?php echo e(__('crud.admin.maps.heatMap')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('heading'); ?>
    <?php echo e(__('crud.admin.maps.heatMap')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('startScripts'); ?>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?><!DOCTYPE html>
    <div id="floating-panel" class="flex justify-center relative">
        <div>
            <button class="rounded bg-blue-500 text-white p-2 m-2" onclick="toggleHeatmap()">Toggle Heatmap</button>
        </div>
        <div>
            <button class="rounded bg-blue-500 text-white p-2 m-2" onclick="changeGradient()">Change gradient</button>
        </div>
        <div>
            <button class="rounded bg-blue-500 text-white p-2 m-2" onclick="changeRadius()">Change radius</button>
        </div>
        <div>
            <button class="rounded bg-blue-500 text-white p-2 m-2" onclick="changeOpacity()">Change opacity</button>
        </div>
    </div>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-6">
        <div class="w-full overflow-x-auto">
            <div id="map" style="height: 500px"></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('endScripts'); ?>

    <script>
        let map, heatmap;

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 10,
                center: { lat: <?php echo e($locations[0]['lat'] ?? "0"); ?>, lng: <?php echo e($locations[0]['lng'] ?? "0"); ?> },
                mapTypeId: "terrain",
            });
            heatmap = new google.maps.visualization.HeatmapLayer({
                data: getPoints(),
                map: map,
            });
        }

        function toggleHeatmap() {
            heatmap.setMap(heatmap.getMap() ? null : map);
        }

        function changeGradient() {
            const gradient = [
                "rgba(0, 255, 255, 0)",
                "rgba(0, 255, 255, 1)",
                "rgba(0, 191, 255, 1)",
                "rgba(0, 127, 255, 1)",
                "rgba(0, 63, 255, 1)",
                "rgba(0, 0, 255, 1)",
                "rgba(0, 0, 223, 1)",
                "rgba(0, 0, 191, 1)",
                "rgba(0, 0, 159, 1)",
                "rgba(0, 0, 127, 1)",
                "rgba(63, 0, 91, 1)",
                "rgba(127, 0, 63, 1)",
                "rgba(191, 0, 31, 1)",
                "rgba(255, 0, 0, 1)",
            ];
            heatmap.set("gradient", heatmap.get("gradient") ? null : gradient);
        }

        function changeRadius() {
            heatmap.set("radius", heatmap.get("radius") ? null : 20);
        }

        function changeOpacity() {
        heatmap.set("opacity", heatmap.get("opacity") ? null : 0.2);
        }

        // Heatmap data: 500 Points
        function getPoints() {
            return [
                <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    new google.maps.LatLng(<?php echo e($loc['lat'] ?? "0"); ?>, <?php echo e($loc['lng'] ?? "0"); ?>),
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
            ];
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(config('constants.map_key')); ?>&callback=initMap&libraries=visualization&v=weekly" async></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH X:\projects\2021 Cab\juneserver\resources\views/admin/map/heatMap.blade.php ENDPATH**/ ?>